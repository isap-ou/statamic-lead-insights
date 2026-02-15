<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Isapp\LeadInsights\Support\Settings;
use Isapp\LeadInsights\Support\SubmissionQuery;
use Statamic\Http\Controllers\CP\CpController;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Handles CSV export of aggregated lead insights data.
 *
 * Pro edition only. Requires "export lead insights" permission.
 */
class ExportController extends CpController
{
    public function __construct(
        Request $request,
        private readonly Settings $settings,
    ) {
        parent::__construct($request);
    }

    /**
     * Export aggregated attribution data as CSV.
     *
     * Accepts query params:
     * - type: source|campaign|form|form_source (required)
     * - days: int (optional, defaults to setting)
     * - form: string (required when type=form_source)
     */
    public function __invoke(Request $request): StreamedResponse
    {
        // Defense-in-depth: route is not registered for Free, but guard against direct URL access
        abort_unless(
            $this->settings->isPro,
            403,
            __('statamic-lead-insights::messages.edition.pro_required'),
        );

        abort_unless(Gate::allows('export lead insights'), 403);

        $request->validate([
            'type' => 'required|in:source,campaign,form,form_source',
            'days' => 'nullable|integer|min:1',
            'form' => 'required_if:type,form_source|nullable|string',
        ]);

        $type = $request->input('type');
        $days = (int) ($request->input('days') ?? $this->settings->defaultDateRangeDays);
        $formHandle = $request->input('form');

        $submissions = SubmissionQuery::submissionsForDateRange($days, $type === 'form_source' ? $formHandle : null);

        $rows = match ($type) {
            'source' => SubmissionQuery::aggregateByField($submissions, $this->settings->attributionKey, 'utm_source', $this->settings->topN),
            'campaign' => SubmissionQuery::aggregateByField($submissions, $this->settings->attributionKey, 'utm_campaign', $this->settings->topN),
            'form' => SubmissionQuery::aggregateByForm($submissions, $this->settings->topN),
            'form_source' => SubmissionQuery::aggregateByField($submissions, $this->settings->attributionKey, 'utm_source', $this->settings->topN),
        };

        $filename = "lead-insights-{$type}-{$days}d.csv";

        return $this->streamCsv($filename, $rows, $submissions->count());
    }

    /**
     * Stream CSV response with header row and data rows.
     */
    private function streamCsv(string $filename, iterable $rows, int $total): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows, $total) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                __('statamic-lead-insights::messages.export.label'),
                __('statamic-lead-insights::messages.export.leads'),
                __('statamic-lead-insights::messages.export.share'),
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['label'],
                    $row['count'],
                    $total > 0 ? round($row['count'] / $total * 100, 1) : 0,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
