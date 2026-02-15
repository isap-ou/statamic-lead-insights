<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Widgets;

use Isapp\LeadInsights\Support\Settings;
use Isapp\LeadInsights\Support\SubmissionQuery;
use Statamic\Widgets\Widget;

/**
 * Dashboard widget: Form → Source Breakdown.
 *
 * Shows source breakdown for a specific form selected in widget config.
 * Pro edition only.
 */
class FormSourceWidget extends Widget
{
    protected static $handle = 'form_source_breakdown';

    public function html()
    {
        $settings = app(Settings::class);
        $formHandle = $this->config('form');

        if (! $formHandle) {
            return view('lead-insights::widgets.no-config', [
                'message' => __('statamic-lead-insights::messages.widgets.no_config'),
            ]);
        }

        $submissions = SubmissionQuery::submissionsForDateRange(
            $settings->defaultDateRangeDays,
            $formHandle,
        );

        $rows = SubmissionQuery::aggregateByField(
            $submissions,
            $settings->attributionKey,
            'utm_source',
            $settings->topN,
        );

        return view('lead-insights::widgets.table', [
            'title' => __('statamic-lead-insights::messages.widgets.sources_for_form', ['form' => $formHandle]),
            'rows' => $rows,
            'total' => $submissions->count(),
            'days' => $settings->defaultDateRangeDays,
        ]);
    }
}
