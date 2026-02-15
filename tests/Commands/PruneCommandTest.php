<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Tests\Commands;

use Isapp\LeadInsights\Support\Settings;
use Isapp\LeadInsights\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;

/**
 * Tests for lead-insights:prune command.
 *
 * Covers: old attribution stripped, recent data untouched.
 * Uses saveQuietly() to bypass the EnrichFormSubmission listener
 * which would overwrite the manually set attribution data.
 */
class PruneCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(Settings::class, fn () => Settings::fromArray([
            'enabled' => true,
            'attribution_key' => '__attribution',
            'retention_days' => 30,
        ]));

        Form::make('contact')->title('Contact')->save();
    }

    #[Test]
    public function it_strips_attribution_from_old_submissions(): void
    {
        $form = Form::find('contact');

        // Old submission (60 days ago) — saveQuietly to bypass listener
        $old = $form->makeSubmission();
        $old->id(now()->subDays(60)->timestamp);
        $old->set('name', 'Old Lead');
        $old->set('__attribution', ['utm_source' => 'google', 'attribution_version' => 1]);
        $old->saveQuietly();

        // Recent submission — saveQuietly to bypass listener
        $recent = $form->makeSubmission();
        $recent->id(now()->timestamp);
        $recent->set('name', 'Recent Lead');
        $recent->set('__attribution', ['utm_source' => 'facebook', 'attribution_version' => 1]);
        $recent->saveQuietly();

        $this->artisan('lead-insights:prune')
            ->expectsOutputToContain('1 submission(s)')
            ->assertExitCode(0);

        // Refresh from storage
        $oldRefreshed = $form->submission($old->id());
        $recentRefreshed = $form->submission($recent->id());

        // Old: attribution stripped, submission preserved
        $this->assertNull($oldRefreshed->get('__attribution'));
        $this->assertSame('Old Lead', $oldRefreshed->get('name'));

        // Recent: attribution untouched
        $this->assertNotNull($recentRefreshed->get('__attribution'));
        $this->assertSame('facebook', $recentRefreshed->get('__attribution')['utm_source']);
    }

    #[Test]
    public function it_respects_days_option_override(): void
    {
        $form = Form::find('contact');

        // Submission 10 days ago
        $submission = $form->makeSubmission();
        $submission->id(now()->subDays(10)->timestamp);
        $submission->set('name', 'Lead');
        $submission->set('__attribution', ['utm_source' => 'google']);
        $submission->saveQuietly();

        // Default retention is 30 days — should not prune
        $this->artisan('lead-insights:prune')
            ->expectsOutputToContain('0 submission(s)')
            ->assertExitCode(0);

        // Override to 5 days — should prune
        $this->artisan('lead-insights:prune', ['--days' => 5])
            ->expectsOutputToContain('1 submission(s)')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_skips_submissions_without_attribution(): void
    {
        $form = Form::find('contact');

        // Old submission without attribution
        $submission = $form->makeSubmission();
        $submission->id(now()->subDays(60)->timestamp);
        $submission->set('name', 'No Attribution');
        $submission->saveQuietly();

        $this->artisan('lead-insights:prune')
            ->expectsOutputToContain('0 submission(s)')
            ->assertExitCode(0);
    }
}
