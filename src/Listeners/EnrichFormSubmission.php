<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Listeners;

use Isapp\LeadInsights\Support\AttributionPayload;
use Isapp\LeadInsights\Support\Settings;
use Statamic\Events\SubmissionCreating;

/**
 * Listener that injects the attribution payload into a form submission.
 *
 * Fires before the submission is persisted to storage.
 * Reads attribution data from the cookie set by CaptureAttribution middleware.
 * Respects consent settings: without consent, attaches only landing_url if configured.
 */
class EnrichFormSubmission
{
    public function __construct(
        private readonly Settings $settings,
    ) {}

    /**
     * Handle the SubmissionCreating event.
     *
     * Reads the attribution cookie and merges the payload into the submission
     * under the configured attribution key (default: __attribution).
     */
    public function handle(SubmissionCreating $event): void
    {
        if (! $this->settings->enabled) {
            return;
        }

        $payload = $this->resolvePayload();

        if ($payload === null) {
            return;
        }

        $event->submission->set(
            $this->settings->attributionKey,
            $payload->toArray(),
        );
    }

    /**
     * Resolve the attribution payload from the cookie.
     *
     * With consent: returns the full payload from the attribution cookie.
     * Without consent: returns a minimal payload with landing_url only (if configured).
     */
    private function resolvePayload(): ?AttributionPayload
    {
        $raw = request()->cookie($this->settings->cookieName);

        // Cookie exists — consent was given when it was set
        if ($raw !== null) {
            $data = json_decode($raw, true);

            if (\is_array($data)) {
                return AttributionPayload::fromArray($data);
            }
        }

        // No cookie — check if we can store minimal data without consent
        if ($this->settings->storeLandingWithoutConsent) {
            return new AttributionPayload(
                landingUrl: request()->fullUrl(),
                lastSeenAt: now()->toIso8601String(),
            );
        }

        return null;
    }
}
