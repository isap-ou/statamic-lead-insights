@use('Illuminate\Support\Facades\Gate')
<div class="card p-0">
    <div class="flex justify-between items-center p-4">
        <h2 class="font-bold text-lg">{{ $title }}</h2>
        <div class="flex items-center gap-2">
            @if(isset($exportType) && Gate::allows('export lead insights'))
                <a href="{{ cp_route('lead-insights.export', array_filter(['type' => $exportType, 'days' => $days, 'form' => $exportForm ?? null])) }}"
                   class="text-blue-500 hover:text-blue-700 text-sm">
                    {{ __('statamic-lead-insights::messages.widgets.export_csv') }}
                </a>
            @endif
            <span class="text-gray-600 text-sm">{{ __('statamic-lead-insights::messages.widgets.last_n_days', ['days' => $days]) }}</span>
        </div>
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
                    <th style="text-align: right">{{ __('statamic-lead-insights::messages.widgets.leads') }}</th>
                    <th style="text-align: right">{{ __('statamic-lead-insights::messages.widgets.share') }}</th>
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
