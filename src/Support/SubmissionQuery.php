<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Statamic\Facades\Form;

/**
 * Queries form submissions and aggregates attribution data for widgets.
 *
 * Provides methods to fetch submissions within a date range,
 * extract attribution payloads, and group/count by a given field.
 */
class SubmissionQuery
{
    /**
     * Get all submissions across all forms within a date range.
     *
     * @return Collection<int, \Statamic\Forms\Submission>
     */
    public static function submissionsForDateRange(int $days, ?string $formHandle = null): Collection
    {
        $since = Carbon::now()->subDays($days)->startOfDay();

        // Get forms to query
        $forms = $formHandle !== null
            ? collect([Form::find($formHandle)])->filter()
            : Form::all();

        // Collect submissions and filter by date using Carbon comparison
        return $forms->flatMap(
            fn ($form) => $form->submissions()->filter(
                fn ($submission) => $submission->date()->gte($since)
            )
        );
    }

    /**
     * Aggregate submissions by an attribution field.
     *
     * Groups by the given field, counts per group, sorts descending,
     * limits to top N, and buckets missing values as "(none)".
     *
     * @return Collection<int, array{label: string, count: int}>
     */
    public static function aggregateByField(
        Collection $submissions,
        string $attributionKey,
        string $field,
        int $topN,
    ): Collection {
        return $submissions
            ->map(fn ($submission) => $submission->get($attributionKey))
            ->filter(fn ($attribution) => \is_array($attribution))
            ->groupBy(fn ($attribution) => $attribution[$field] ?? '(none)')
            ->map(fn ($group, $label) => [
                'label' => $label === '' ? '(none)' : $label,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take($topN)
            ->values();
    }

    /**
     * Aggregate submissions by form handle.
     *
     * @return Collection<int, array{label: string, count: int}>
     */
    public static function aggregateByForm(
        Collection $submissions,
        int $topN,
    ): Collection {
        return $submissions
            ->groupBy(fn ($submission) => $submission->form()->handle())
            ->map(fn ($group, $handle) => [
                'label' => $handle,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take($topN)
            ->values();
    }
}
