<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Isapp\LeadInsights\Support\Settings;
use Isapp\LeadInsights\Support\SubmissionQuery;
use Statamic\Http\Controllers\CP\CpController;

/**
 * Returns aggregated widget data as JSON for async loading.
 *
 * Accepts type (source|campaign|form|form_source), optional days and form params.
 */
class WidgetDataController extends CpController
{
    public function __construct(
        Request $request,
        private readonly Settings $settings,
    ) {
        parent::__construct($request);
    }

    /**
     * Return aggregated attribution data for a widget.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:source,campaign,form,form_source',
            'days' => 'nullable|integer|min:1',
            'form' => 'required_if:type,form_source|nullable|string',
        ]);

        $type = $request->input('type');
        $days = (int) ($request->input('days') ?? $this->settings->defaultDateRangeDays);
        $formHandle = $request->input('form');

        $submissions = SubmissionQuery::submissionsForDateRange(
            $days,
            $type === 'form_source' ? $formHandle : null,
        );

        $rows = match ($type) {
            'source' => SubmissionQuery::aggregateByField($submissions, $this->settings->attributionKey, 'utm_source', $this->settings->topN),
            'campaign' => SubmissionQuery::aggregateByField($submissions, $this->settings->attributionKey, 'utm_campaign', $this->settings->topN),
            'form' => SubmissionQuery::aggregateByForm($submissions, $this->settings->topN),
            'form_source' => SubmissionQuery::aggregateByField($submissions, $this->settings->attributionKey, 'utm_source', $this->settings->topN),
        };

        return response()->json([
            'rows' => $rows->values()->toArray(),
            'total' => $submissions->count(),
        ]);
    }
}
