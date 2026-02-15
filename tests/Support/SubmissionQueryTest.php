<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Tests\Support;

use Isapp\LeadInsights\Support\SubmissionQuery;
use Isapp\LeadInsights\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;

/**
 * Tests for SubmissionQuery aggregation logic.
 *
 * Covers: correct counts by source, (none) bucketing, top N limiting,
 * aggregation by form handle, skipping submissions without attribution.
 */
class SubmissionQueryTest extends TestCase
{
    private int $idCounter = 1000000000;

    protected function setUp(): void
    {
        parent::setUp();

        Form::make('contact')->title('Contact')->save();
        Form::make('newsletter')->title('Newsletter')->save();
    }

    #[Test]
    public function it_aggregates_by_utm_source_with_correct_counts(): void
    {
        $form = Form::find('contact');

        // Build a collection of submissions directly (no Stache round-trip)
        $submissions = collect([
            $this->makeSubmission($form, ['utm_source' => 'google']),
            $this->makeSubmission($form, ['utm_source' => 'google']),
            $this->makeSubmission($form, ['utm_source' => 'facebook']),
        ]);

        $rows = SubmissionQuery::aggregateByField($submissions, '__attribution', 'utm_source', 10);

        $this->assertCount(2, $rows);
        $this->assertSame('google', $rows[0]['label']);
        $this->assertSame(2, $rows[0]['count']);
        $this->assertSame('facebook', $rows[1]['label']);
        $this->assertSame(1, $rows[1]['count']);
    }

    #[Test]
    public function it_buckets_missing_source_as_none(): void
    {
        $form = Form::find('contact');

        $submissions = collect([
            $this->makeSubmission($form, ['utm_source' => 'google']),
            $this->makeSubmission($form, ['utm_source' => null]),
            $this->makeSubmission($form, []),
        ]);

        $rows = SubmissionQuery::aggregateByField($submissions, '__attribution', 'utm_source', 10);

        $noneRow = $rows->firstWhere('label', '(none)');
        $this->assertNotNull($noneRow);
        $this->assertSame(2, $noneRow['count']);
    }

    #[Test]
    public function it_limits_to_top_n(): void
    {
        $form = Form::find('contact');

        $submissions = collect([
            $this->makeSubmission($form, ['utm_source' => 'a']),
            $this->makeSubmission($form, ['utm_source' => 'b']),
            $this->makeSubmission($form, ['utm_source' => 'c']),
        ]);

        $rows = SubmissionQuery::aggregateByField($submissions, '__attribution', 'utm_source', 2);

        $this->assertCount(2, $rows);
    }

    #[Test]
    public function it_aggregates_by_form_handle(): void
    {
        $contact = Form::find('contact');
        $newsletter = Form::find('newsletter');

        $submissions = collect([
            $this->makeSubmission($contact, ['utm_source' => 'google']),
            $this->makeSubmission($contact, ['utm_source' => 'google']),
            $this->makeSubmission($newsletter, ['utm_source' => 'facebook']),
        ]);

        $rows = SubmissionQuery::aggregateByForm($submissions, 10);

        $this->assertCount(2, $rows);
        $this->assertSame('contact', $rows[0]['label']);
        $this->assertSame(2, $rows[0]['count']);
    }

    #[Test]
    public function it_skips_submissions_without_attribution(): void
    {
        $form = Form::find('contact');

        // One with attribution, one without
        $withAttribution = $this->makeSubmission($form, ['utm_source' => 'google']);

        $plain = $form->makeSubmission();
        $plain->id($this->nextId());
        $plain->set('name', 'No Attribution');

        $submissions = collect([$withAttribution, $plain]);

        $rows = SubmissionQuery::aggregateByField($submissions, '__attribution', 'utm_source', 10);

        $this->assertCount(1, $rows);
        $this->assertSame('google', $rows[0]['label']);
    }

    /**
     * Create a submission object with attribution data (not persisted to Stache).
     */
    private function makeSubmission($form, array $attribution): \Statamic\Forms\Submission
    {
        $submission = $form->makeSubmission();
        $submission->id($this->nextId());
        $submission->set('name', 'Test');
        $submission->set('__attribution', array_merge($attribution, ['attribution_version' => 1]));

        return $submission;
    }

    /**
     * Generate a unique submission ID (timestamp-based).
     */
    private function nextId(): int
    {
        return $this->idCounter++;
    }
}
