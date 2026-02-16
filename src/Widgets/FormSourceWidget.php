<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Widgets;

use Illuminate\Support\Facades\Gate;
use Isapp\LeadInsights\Support\Settings;
use Statamic\Widgets\VueComponent;
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

    public function component()
    {
        $settings = app(Settings::class);
        $formHandle = $this->config('form');

        if (! $formHandle) {
            return VueComponent::render('lead-insights-table', [
                'title' => __('statamic-lead-insights::messages.widgets.sources_for_form', ['form' => '']),
                'message' => __('statamic-lead-insights::messages.widgets.no_config'),
            ]);
        }

        return VueComponent::render('lead-insights-table', [
            'title' => __('statamic-lead-insights::messages.widgets.sources_for_form', ['form' => $formHandle]),
            'dataUrl' => cp_route('lead-insights.data', [
                'type' => 'form_source',
                'days' => $settings->defaultDateRangeDays,
                'form' => $formHandle,
            ]),
            'days' => $settings->defaultDateRangeDays,
            'exportUrl' => $this->exportUrl('form_source', $settings->defaultDateRangeDays, $formHandle),

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
    private function exportUrl(string $type, int $days, ?string $form = null): ?string
    {
        if (! Gate::allows('export lead insights')) {
            return null;
        }

        return cp_route('lead-insights.export', array_filter([
            'type' => $type,
            'days' => $days,
            'form' => $form,
        ]));
    }
}
