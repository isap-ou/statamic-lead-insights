<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Widgets;

use Isapp\LeadInsights\Support\Settings;
use Isapp\LeadInsights\Support\SubmissionQuery;
use Statamic\Widgets\Widget;

/**
 * Dashboard widget: Leads by Campaign.
 *
 * Groups form submissions by utm_campaign for the configured date range.
 * Pro edition only.
 */
class LeadsByCampaignWidget extends Widget
{
    protected static $handle = 'leads_by_campaign';

    public function html()
    {
        $settings = app(Settings::class);
        $submissions = SubmissionQuery::submissionsForDateRange($settings->defaultDateRangeDays);

        $rows = SubmissionQuery::aggregateByField(
            $submissions,
            $settings->attributionKey,
            'utm_campaign',
            $settings->topN,
        );

        return view('lead-insights::widgets.table', [
            'title' => __('statamic-lead-insights::messages.widgets.leads_by_campaign'),
            'rows' => $rows,
            'total' => $submissions->count(),
            'days' => $settings->defaultDateRangeDays,
        ]);
    }
}
