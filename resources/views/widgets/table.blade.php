<div class="card p-0">
    <div class="flex justify-between items-center p-4">
        <h2 class="font-bold text-lg">{{ $title }}</h2>
        <span class="text-gray-600 text-sm">{{ __('statamic-lead-insights::messages.widgets.last_n_days', ['days' => $days]) }}</span>
    </div>

    @if($rows->isEmpty())
        <div class="p-4 text-gray-500 text-sm">
            {{ __('statamic-lead-insights::messages.widgets.no_data') }}
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('statamic-lead-insights::messages.widgets.label') }}</th>
                    <th class="text-right">{{ __('statamic-lead-insights::messages.widgets.leads') }}</th>
                    <th class="text-right">{{ __('statamic-lead-insights::messages.widgets.share') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td class="text-right">{{ $row['count'] }}</td>
                        <td class="text-right">
                            {{ $total > 0 ? round($row['count'] / $total * 100, 1) : 0 }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
