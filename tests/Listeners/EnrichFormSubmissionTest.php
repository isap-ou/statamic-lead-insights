<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Tests\Listeners;

use Isapp\LeadInsights\Support\AttributionPayload;
use Isapp\LeadInsights\Support\Settings;
use Isapp\LeadInsights\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;

/**
 * Tests for EnrichFormSubmission listener.
 *
 * Covers: payload attached with consent, landing_url only without consent,
 * no data when store_landing_without_consent is off.
 */
class EnrichFormSubmissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Form::make('contact')->title('Contact')->save();
    }

    #[Test]
    public function it_attaches_attribution_from_cookie_to_submission(): void
    {
        $this->app->singleton(Settings::class, fn () => Settings::fromArray([
            'enabled' => true,
            'attribution_key' => '__attribution',
            'cookie_name' => 'lead_insights_attribution',
        ]));

        $payload = new AttributionPayload(
            utmSource: 'google',
            utmMedium: 'cpc',
            lastSeenAt: now()->toIso8601String(),
        );

        // Set the cookie on the incoming request so the listener can read it
        $this->app['request']->cookies->set(
            'lead_insights_attribution',
            json_encode($payload->toArray()),
        );

        $form = Form::find('contact');
        $submission = $form->makeSubmission();
        $submission->set('name', 'Test User');
        $submission->save();

        $attribution = $submission->get('__attribution');
        $this->assertNotNull($attribution);
        $this->assertSame('google', $attribution['utm_source']);
        $this->assertSame('cpc', $attribution['utm_medium']);
    }

    #[Test]
    public function it_attaches_landing_url_without_consent_when_configured(): void
    {
        $this->app->singleton(Settings::class, fn () => Settings::fromArray([
            'enabled' => true,
            'attribution_key' => '__attribution',
            'cookie_name' => 'lead_insights_attribution',
            'store_landing_without_consent' => true,
        ]));

        // No attribution cookie — simulates no consent
        $form = Form::find('contact');
        $submission = $form->makeSubmission();
        $submission->set('name', 'Test User');
        $submission->save();

        $attribution = $submission->get('__attribution');
        $this->assertNotNull($attribution);
        $this->assertNotNull($attribution['landing_url']);
        $this->assertNotNull($attribution['last_seen_at']);
        $this->assertNull($attribution['utm_source']);
    }

    #[Test]
    public function it_attaches_nothing_when_disabled(): void
    {
        $this->app->singleton(Settings::class, fn () => Settings::fromArray([
            'enabled' => false,
            'attribution_key' => '__attribution',
            'cookie_name' => 'lead_insights_attribution',
        ]));

        $form = Form::find('contact');
        $submission = $form->makeSubmission();
        $submission->set('name', 'Test User');
        $submission->save();

        $this->assertNull($submission->get('__attribution'));
    }

    #[Test]
    public function it_does_not_break_existing_submission_data(): void
    {
        $this->app->singleton(Settings::class, fn () => Settings::fromArray([
            'enabled' => true,
            'attribution_key' => '__attribution',
            'cookie_name' => 'lead_insights_attribution',
            'store_landing_without_consent' => true,
        ]));

        $form = Form::find('contact');
        $submission = $form->makeSubmission();
        $submission->set('name', 'Test User');
        $submission->set('email', 'test@example.com');
        $submission->save();

        // Original fields are preserved
        $this->assertSame('Test User', $submission->get('name'));
        $this->assertSame('test@example.com', $submission->get('email'));
    }
}
