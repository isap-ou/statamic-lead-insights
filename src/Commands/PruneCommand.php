<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Isapp\LeadInsights\Support\Settings;
use Statamic\Facades\Form;

/**
 * Artisan command to prune old attribution data from form submissions.
 *
 * Strips the attribution payload from submissions older than the configured
 * retention period. Preserves the submission itself — only removes attribution data.
 * Pro edition only.
 */
class PruneCommand extends Command
{
    protected $signature = 'lead-insights:prune
        {--days= : Number of days to retain (defaults to addon setting)}';

    protected $description = 'Strip attribution data from form submissions older than the retention period';

    public function handle(Settings $settings): int
    {
        $days = (int) ($this->option('days') ?? $settings->retentionDays);
        $cutoff = Carbon::now()->subDays($days)->startOfDay();
        $attributionKey = $settings->attributionKey;

        $pruned = 0;

        Form::all()->each(function ($form) use ($cutoff, $attributionKey, &$pruned) {
            $form->submissions()
                ->filter(fn ($submission) => $submission->date()->lt($cutoff))
                ->each(function ($submission) use ($attributionKey, &$pruned) {
                    if ($submission->get($attributionKey) === null) {
                        return;
                    }

                    $submission->remove($attributionKey)->save();
                    $pruned++;
                });
        });

        $this->info(__('statamic-lead-insights::messages.commands.prune_result', ['count' => $pruned, 'days' => $days]));

        return self::SUCCESS;
    }
}
