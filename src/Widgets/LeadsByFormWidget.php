<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Widgets;

use Isapp\LeadInsights\Support\Settings;
use Isapp\LeadInsights\Support\SubmissionQuery;
use Statamic\Widgets\Widget;

/**
 * Dashboard widget: Leads by Form.
 *
 * Groups form submissions by form handle for the configured date range.
 * Pro edition only.
 */
class LeadsByFormWidget extends Widget
{
    protected static $handle = 'leads_by_form';

    public function html()
    {
        $settings = app(Settings::class);
        $submissions = SubmissionQuery::submissionsForDateRange($settings->defaultDateRangeDays);

        $rows = SubmissionQuery::aggregateByForm($submissions, $settings->topN);

        return view('lead-insights::widgets.table', [
            'title' => __('statamic-lead-insights::messages.widgets.leads_by_form'),
            'rows' => $rows,
            'total' => $submissions->count(),
            'days' => $settings->defaultDateRangeDays,
        ]);
    }
}
