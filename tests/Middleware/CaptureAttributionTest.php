<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Tests\Middleware;

use Isapp\LeadInsights\Support\Settings;
use Isapp\LeadInsights\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Tests for CaptureAttribution middleware.
 *
 * Covers: consent gating, UTM capture with consent, no cookie without consent.
 */
class CaptureAttributionTest extends TestCase
{
    protected function resolveApplicationConfiguration($app): void
    {
        parent::resolveApplicationConfiguration($app);

        // Register a test route through the web middleware stack
        $app['router']->middleware('web')->get('/__test', fn () => response('ok'));
    }

    #[Test]
    public function it_sets_attribution_cookie_when_consent_given(): void
    {
        $this->withSettings([
            'enabled' => true,
            'consent_required' => true,
            'consent_cookie_name' => 'cookie_consent',
            'cookie_name' => 'lead_insights_attribution',
        ]);

        // withCookie encrypts the value; EncryptCookies middleware decrypts it
        $response = $this->withCookie('cookie_consent', '1')
            ->get('/__test?utm_source=google&utm_medium=cpc');

        $response->assertStatus(200);
        $response->assertCookie('lead_insights_attribution');
    }

    #[Test]
    public function it_does_not_set_cookie_without_consent(): void
    {
        $this->withSettings([
            'enabled' => true,
            'consent_required' => true,
            'consent_cookie_name' => 'cookie_consent',
            'cookie_name' => 'lead_insights_attribution',
        ]);

        // No consent cookie — middleware should not set attribution cookie
        $response = $this->get('/__test?utm_source=google');

        $response->assertStatus(200);
        $response->assertCookieMissing('lead_insights_attribution');
    }

    #[Test]
    public function it_sets_cookie_when_consent_not_required(): void
    {
        $this->withSettings([
            'enabled' => true,
            'consent_required' => false,
            'cookie_name' => 'lead_insights_attribution',
        ]);

        $response = $this->get('/__test?utm_source=facebook');

        $response->assertStatus(200);
        $response->assertCookie('lead_insights_attribution');
    }

    #[Test]
    public function it_skips_when_addon_disabled(): void
    {
        $this->withSettings([
            'enabled' => false,
            'consent_required' => false,
            'cookie_name' => 'lead_insights_attribution',
        ]);

        $response = $this->get('/__test?utm_source=google');

        $response->assertStatus(200);
        $response->assertCookieMissing('lead_insights_attribution');
    }

    #[Test]
    public function it_checks_consent_cookie_value_when_configured(): void
    {
        $this->withSettings([
            'enabled' => true,
            'consent_required' => true,
            'consent_cookie_name' => 'cookie_consent',
            'consent_cookie_value' => 'accepted',
            'cookie_name' => 'lead_insights_attribution',
        ]);

        // Wrong value — no consent
        $response = $this->withCookie('cookie_consent', 'declined')
            ->get('/__test?utm_source=google');

        $response->assertCookieMissing('lead_insights_attribution');

        // Correct value — consent granted
        $response = $this->withCookie('cookie_consent', 'accepted')
            ->get('/__test?utm_source=google');

        $response->assertCookie('lead_insights_attribution');
    }

    /**
     * Override Settings singleton for the test.
     */
    private function withSettings(array $values): void
    {
        $this->app->singleton(Settings::class, fn () => Settings::fromArray($values));
    }
}
