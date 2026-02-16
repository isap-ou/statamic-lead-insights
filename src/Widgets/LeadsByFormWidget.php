<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Widgets;

use Illuminate\Support\Facades\Gate;
use Isapp\LeadInsights\Support\Settings;
use Statamic\Widgets\VueComponent;
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

    public function component()
    {
        $settings = app(Settings::class);

        return VueComponent::render('lead-insights-table', [
            'title' => __('statamic-lead-insights::messages.widgets.leads_by_form'),
            'dataUrl' => cp_route('lead-insights.data', [
                'type' => 'form',
                'days' => $settings->defaultDateRangeDays,
            ]),
            'days' => $settings->defaultDateRangeDays,
            'exportUrl' => $this->exportUrl('form', $settings->defaultDateRangeDays),

            'labels' => [
                'label' => __('statamic-lead-insights::messages.widgets.label'),
                'leads' => __('statamic-lead-insights::messages.widgets.leads'),
                'share' => __('statamic-lead-insights::messages.widgets.share'),
                'lastNDays' => __('statamic-lead-insights::messages.widgets.last_n_days', ['days' => $settings->defaultDateRangeDays]),
                'exportCsv' => __('statamic-lead-insights::messages.widgets.export_csv'),
            ],
        ]);
    }

    /**
     * Build the CSV export URL if the user has permission and the edition supports it.
     */
    private function exportUrl(string $type, int $days): ?string
    {
        if (! Gate::allows('export lead insights')) {
            return null;
        }

        return cp_route('lead-insights.export', ['type' => $type, 'days' => $days]);
    }
}
