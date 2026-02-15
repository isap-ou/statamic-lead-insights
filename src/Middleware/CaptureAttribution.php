<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Isapp\LeadInsights\Support\AttributionPayload;
use Isapp\LeadInsights\Support\Settings;

/**
 * Middleware that captures marketing attribution data from frontend requests.
 *
 * Reads UTM query parameters, Referer header, and current URL.
 * Persists attribution state in a first-party cookie when consent is given.
 * Respects EU-first consent gating: without consent, no cookie is set
 * and no UTM data is stored (only landing_url if configured).
 */
class CaptureAttribution
{
    public function __construct(
        private readonly Settings $settings,
    ) {}

    /**
     * Handle an incoming frontend request.
     *
     * Captures attribution data and attaches the cookie to the response.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Addon is disabled — skip entirely
        if (! $this->settings->enabled) {
            return $next($request);
        }

        $response = $next($request);

        // Only attach cookies to standard responses
        if (! $response instanceof Response) {
            return $response;
        }

        $hasConsent = $this->hasConsent($request);
        $existing = $this->readCookie($request);
        $payload = $this->buildPayload($request, $existing, $hasConsent);

        // Without consent: do not set cookie
        if (! $hasConsent) {
            return $response;
        }

        // Nothing to persist
        if ($payload === null) {
            return $response;
        }

        // Attach cookie directly to the response
        $response->headers->setCookie($this->makeCookie($payload));

        return $response;
    }

    /**
     * Build the attribution payload from the current request.
     *
     * With consent: capture UTMs, referrer, landing URL, timestamps.
     * Without consent: no payload (cookie will not be set).
     */
    private function buildPayload(
        Request $request,
        ?AttributionPayload $existing,
        bool $hasConsent,
    ): ?AttributionPayload {
        if (! $hasConsent) {
            return null;
        }

        $now = now()->toIso8601String();

        $incoming = new AttributionPayload(
            utmSource: $request->query('utm_source'),
            utmMedium: $request->query('utm_medium'),
            utmCampaign: $request->query('utm_campaign'),
            utmTerm: $request->query('utm_term'),
            utmContent: $request->query('utm_content'),
            referrer: $request->header('referer'),
            landingUrl: $request->fullUrl(),
            firstSeenAt: $now,
            lastSeenAt: $now,
        );

        // No existing cookie — first visit with consent
        if ($existing === null) {
            return $incoming;
        }

        // Has UTMs — update last-touch, preserve first-touch
        if ($incoming->hasUtm()) {
            return $existing
                ->mergeLastTouch($incoming)
                ->withFirstTouch($incoming);
        }

        // No UTMs on this request — keep existing, only update last_seen_at
        return new AttributionPayload(
            utmSource: $existing->utmSource,
            utmMedium: $existing->utmMedium,
            utmCampaign: $existing->utmCampaign,
            utmTerm: $existing->utmTerm,
            utmContent: $existing->utmContent,
            referrer: $existing->referrer,
            landingUrl: $existing->landingUrl,
            firstSeenAt: $existing->firstSeenAt,
            lastSeenAt: $now,
            attributionVersion: AttributionPayload::VERSION,
        );
    }

    /**
     * Check if the user has given marketing consent.
     *
     * If consent is not required (setting), always returns true.
     * Otherwise checks for the configured consent cookie name/value.
     */
    private function hasConsent(Request $request): bool
    {
        if (! $this->settings->consentRequired) {
            return true;
        }

        // No consent cookie configured — treat as no consent
        if ($this->settings->consentCookieName === null) {
            return false;
        }

        $cookieValue = $request->cookie($this->settings->consentCookieName);

        // Cookie not present — no consent
        if ($cookieValue === null) {
            return false;
        }

        // No expected value configured — cookie presence is enough
        if ($this->settings->consentCookieValue === null) {
            return true;
        }

        return $cookieValue === $this->settings->consentCookieValue;
    }

    /**
     * Read and decode the existing attribution cookie.
     */
    private function readCookie(Request $request): ?AttributionPayload
    {
        $raw = $request->cookie($this->settings->cookieName);

        if ($raw === null) {
            return null;
        }

        $data = json_decode($raw, true);

        if (! \is_array($data)) {
            return null;
        }

        return AttributionPayload::fromArray($data);
    }

    /**
     * Create the attribution cookie via Laravel's cookie helper.
     *
     * SameSite=Lax, Secure on HTTPS, HttpOnly.
     */
    private function makeCookie(AttributionPayload $payload): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            name: $this->settings->cookieName,
            value: json_encode($payload->toArray()),
            minutes: $this->settings->cookieTtlDays * 24 * 60,
            sameSite: 'Lax',
        );
    }
}
