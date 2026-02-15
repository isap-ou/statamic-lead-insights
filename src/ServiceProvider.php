<?php

declare(strict_types=1);

namespace Isapp\LeadInsights;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Statamic\Events\SubmissionCreating;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

/**
 * Lead Insights addon service provider.
 *
 * Registers settings blueprint, middleware, listeners, widgets, and commands.
 * Pro-only features (extra widgets, prune command, CSV export) are gated
 * at the registration level — they simply don't register on the Free edition.
 */
class ServiceProvider extends AddonServiceProvider
{
    /** @var class-string[] Widgets that require the Pro edition */
    private const PRO_WIDGETS = [
        Widgets\LeadsByCampaignWidget::class,
        Widgets\LeadsByFormWidget::class,
        Widgets\FormSourceWidget::class,
    ];

    /** @var class-string[] Commands that require the Pro edition */
    private const PRO_COMMANDS = [
        Commands\PruneCommand::class,
    ];

    /** @var array<string, class-string[]> Middleware groups to register */
    protected $middlewareGroups = [
        'web' => [
            Middleware\CaptureAttribution::class,
        ],
    ];

    /** @var array<class-string, class-string[]> Event listeners to register */
    protected $listen = [
        SubmissionCreating::class => [
            Listeners\EnrichFormSubmission::class,
        ],
    ];

    /** @var class-string[] Widgets to register */
    protected $widgets = [
        Widgets\LeadsBySourceWidget::class,
        Widgets\LeadsByCampaignWidget::class,
        Widgets\LeadsByFormWidget::class,
        Widgets\FormSourceWidget::class,
    ];

    /** @var class-string[] Artisan commands to register */
    protected $commands = [
        Commands\PruneCommand::class,
    ];

    /** @var string View namespace for Blade templates */
    protected $viewNamespace = 'lead-insights';

    public function register()
    {
        parent::register();

        $this->app->singleton(Support\Settings::class, fn () => Support\Settings::fromAddon());
    }

    public function bootAddon()
    {
        $this->registerSettings();
        $this->registerPermissions();
        $this->registerRoutes();
    }

    /**
     * Schedule automatic pruning of attribution data (Pro-only).
     */
    protected function schedule(Schedule $schedule): void
    {
        $settings = $this->app->make(Support\Settings::class);

        if (! $settings->isPro || ! $settings->pruneScheduleEnabled) {
            return;
        }

        $command = $schedule->command(Commands\PruneCommand::class);

        match ($settings->pruneScheduleFrequency) {
            'weekly' => $command->weekly(),
            'monthly' => $command->monthly(),
            default => $command->daily(),
        };

        $command->at($settings->pruneScheduleTime);
    }

    /**
     * Check whether the addon is running the Pro edition.
     */
    private function isPro(): bool
    {
        return app(Support\Settings::class)->isPro;
    }

    /**
     * Boot widgets, filtering out Pro-only widgets on the Free edition.
     *
     * Replicates parent AddonServiceProvider::bootWidgets() logic with edition filtering.
     */
    protected function bootWidgets()
    {
        $widgets = collect($this->widgets)
            ->when(! $this->isPro(), fn ($collection) => $collection->reject(
                fn (string $class) => \in_array($class, self::PRO_WIDGETS, true)
            ))
            ->unique();

        foreach ($widgets as $class) {
            $class::register();
        }

        return $this;
    }

    /**
     * Boot commands, filtering out Pro-only commands on the Free edition.
     *
     * Replicates parent AddonServiceProvider::bootCommands() logic with edition filtering.
     */
    protected function bootCommands()
    {
        if ($this->app->runningInConsole()) {
            $commands = collect($this->commands)
                ->when(! $this->isPro(), fn ($collection) => $collection->reject(
                    fn (string $class) => \in_array($class, self::PRO_COMMANDS, true)
                ))
                ->unique()
                ->all();

            $this->commands($commands);
        }

        return $this;
    }

    /**
     * Register custom permissions for the addon.
     *
     * "view lead insights" is available in both editions.
     * "export lead insights" is Pro-only.
     */
    private function registerPermissions(): void
    {
        Permission::extend(function () {
            Permission::register('view lead insights')
                ->label(__('statamic-lead-insights::messages.permissions.view'));

            // Export permission is Pro-only
            if ($this->isPro()) {
                Permission::register('export lead insights')
                    ->label(__('statamic-lead-insights::messages.permissions.export'));
            }
        });
    }

    /**
     * Register CP routes for CSV export (Pro-only).
     */
    private function registerRoutes(): void
    {
        if (! $this->isPro()) {
            return;
        }

        $this->registerCpRoutes(function () {
            Route::get('lead-insights/export', Http\Controllers\ExportController::class)
                ->name('lead-insights.export');
        });
    }

    /**
     * Register the addon settings blueprint for the CP settings UI.
     *
     * Settings are grouped into tabs: General, Consent/GDPR, Reporting, Retention.
     * Values are stored automatically in resources/addons/{slug}.yaml.
     */
    private function registerSettings(): void
    {
        $this->registerSettingsBlueprint([
            'tabs' => [
                'general' => [
                    'display' => __('statamic-lead-insights::messages.settings.tab_general'),
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'enabled',
                                    'field' => [
                                        'type' => 'toggle',
                                        'display' => __('statamic-lead-insights::messages.settings.enabled'),
                                        'instructions' => __('statamic-lead-insights::messages.settings.enabled_instructions'),
                                        'default' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'attribution_key',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('statamic-lead-insights::messages.settings.attribution_key'),
                                        'instructions' => __('statamic-lead-insights::messages.settings.attribution_key_instructions'),
                                        'default' => '__attribution',
                                    ],
                                ],
                                [
                                    'handle' => 'cookie_name',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('statamic-lead-insights::messages.settings.cookie_name'),
                                        'instructions' => __('statamic-lead-insights::messages.settings.cookie_name_instructions'),
                                        'default' => 'lead_insights_attribution',
                                    ],
                                ],
                                [
                                    'handle' => 'cookie_ttl_days',
                                    'field' => [
                                        'type' => 'integer',
                                        'display' => __('statamic-lead-insights::messages.settings.cookie_ttl_days'),
                                        'instructions' => __('statamic-lead-insights::messages.settings.cookie_ttl_days_instructions'),
                                        'default' => 30,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'consent' => [
                    'display' => __('statamic-lead-insights::messages.settings.tab_consent'),
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'consent_required',
                                    'field' => [
                                        'type' => 'toggle',
                                        'display' => __('statamic-lead-insights::messages.settings.consent_required'),
                                        'instructions' => __('statamic-lead-insights::messages.settings.consent_required_instructions'),
                                        'default' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'consent_cookie_name',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('statamic-lead-insights::messages.settings.consent_cookie_name'),
                                        'instructions' => __('statamic-lead-insights::messages.settings.consent_cookie_name_instructions'),
                                    ],
                                ],
                                [
                                    'handle' => 'consent_cookie_value',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('statamic-lead-insights::messages.settings.consent_cookie_value'),
                                        'instructions' => __('statamic-lead-insights::messages.settings.consent_cookie_value_instructions'),
                                    ],
                                ],
                                [
                                    'handle' => 'store_landing_without_consent',
                                    'field' => [
                                        'type' => 'toggle',
                                        'display' => __('statamic-lead-insights::messages.settings.store_landing_without_consent'),
                                        'instructions' => __('statamic-lead-insights::messages.settings.store_landing_without_consent_instructions'),
                                        'default' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'store_referrer_without_consent',
                                    'field' => [
                                        'type' => 'toggle',
                                        'display' => __('statamic-lead-insights::messages.settings.store_referrer_without_consent'),
                                        'instructions' => __('statamic-lead-insights::messages.settings.store_referrer_without_consent_instructions'),
                                        'default' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'reporting' => [
                    'display' => __('statamic-lead-insights::messages.settings.tab_reporting'),
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'top_n',
                                    'field' => [
                                        'type' => 'integer',
                                        'display' => __('statamic-lead-insights::messages.settings.top_n'),
                                        'instructions' => __('statamic-lead-insights::messages.settings.top_n_instructions'),
                                        'default' => 10,
                                    ],
                                ],
                                [
                                    'handle' => 'default_date_range_days',
                                    'field' => [
                                        'type' => 'integer',
                                        'display' => __('statamic-lead-insights::messages.settings.default_date_range_days'),
                                        'instructions' => __('statamic-lead-insights::messages.settings.default_date_range_days_instructions'),
                                        'default' => 30,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                ...$this->retentionTab(),
            ],
        ]);
    }

    /**
     * Build the Retention tab fields, conditionally including Pro-only schedule fields.
     *
     * @return array<string, array<string, mixed>>
     */
    private function retentionTab(): array
    {
        if (! $this->isPro()) {
            return [];
        }

        return [
            'retention' => [
                'display' => __('statamic-lead-insights::messages.settings.tab_retention'),
                'sections' => [
                    [
                        'fields' => [
                            [
                                'handle' => 'retention_days',
                                'field' => [
                                    'type' => 'integer',
                                    'display' => __('statamic-lead-insights::messages.settings.retention_days'),
                                    'instructions' => __('statamic-lead-insights::messages.settings.retention_days_instructions'),
                                    'default' => 365,
                                ],
                            ],
                            [
                                'handle' => 'prune_schedule_enabled',
                                'field' => [
                                    'type' => 'toggle',
                                    'display' => __('statamic-lead-insights::messages.settings.prune_schedule_enabled'),
                                    'instructions' => __('statamic-lead-insights::messages.settings.prune_schedule_enabled_instructions'),
                                    'default' => false,
                                ],
                            ],
                            [
                                'handle' => 'prune_schedule_frequency',
                                'field' => [
                                    'type' => 'select',
                                    'display' => __('statamic-lead-insights::messages.settings.prune_schedule_frequency'),
                                    'instructions' => __('statamic-lead-insights::messages.settings.prune_schedule_frequency_instructions'),
                                    'default' => 'daily',
                                    'options' => [
                                        'daily' => __('statamic-lead-insights::messages.settings.prune_frequency_daily'),
                                        'weekly' => __('statamic-lead-insights::messages.settings.prune_frequency_weekly'),
                                        'monthly' => __('statamic-lead-insights::messages.settings.prune_frequency_monthly'),
                                    ],
                                ],
                            ],
                            [
                                'handle' => 'prune_schedule_time',
                                'field' => [
                                    'type' => 'text',
                                    'display' => __('statamic-lead-insights::messages.settings.prune_schedule_time'),
                                    'instructions' => __('statamic-lead-insights::messages.settings.prune_schedule_time_instructions'),
                                    'default' => '02:00',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
