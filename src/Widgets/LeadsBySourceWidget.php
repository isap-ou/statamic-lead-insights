<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Widgets;

use Isapp\LeadInsights\Support\Settings;
use Isapp\LeadInsights\Support\SubmissionQuery;
use Statamic\Widgets\Widget;

/**
 * Dashboard widget: Leads by Source.
 *
 * Groups form submissions by utm_source for the configured date range.
 * Available in both Free and Pro editions.
 */
class LeadsBySourceWidget extends Widget
{
    protected static $handle = 'leads_by_source';

    public function html()
    {
        $settings = app(Settings::class);
        $submissions = SubmissionQuery::submissionsForDateRange($settings->defaultDateRangeDays);

        $rows = SubmissionQuery::aggregateByField(
            $submissions,
            $settings->attributionKey,
            'utm_source',
            $settings->topN,
        );

        return view('lead-insights::widgets.table', [
            'title' => __('statamic-lead-insights::messages.widgets.leads_by_source'),
            'rows' => $rows,
            'total' => $submissions->count(),
            'days' => $settings->defaultDateRangeDays,
            'exportType' => 'source',
        ]);
    }
}
