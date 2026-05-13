@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'Performance Insights')

@section('content')
<div class="p-6 space-y-5">

    {{-- Date Filter --}}
    <div class="bg-sn-card rounded-2xl p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}"
                       class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
            </div>
            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">To</label>
                <input type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}"
                       class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
            </div>
            <button type="submit" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
                <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i> Generate Report
            </button>
        </form>
    </div>

    {{-- Volume Metrics --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach([
            ['Total Created',    $volumeMetrics['total_created'],  'ticket',       'text-blue-400'],
            ['Total Resolved',   $volumeMetrics['total_resolved'], 'check-circle', 'text-green-400'],
            ['Avg Resolution',   ($volumeMetrics['avg_resolution'] ? $volumeMetrics['avg_resolution'].'h' : 'N/A'), 'clock', 'text-amber-400'],
            ['SLA Compliance',   $volumeMetrics['sla_compliance'].'%', 'trending-up', 'text-primary'],
        ] as [$label, $val, $icon, $color])
        <div class="bg-sn-card p-5 rounded-xl">
            <div class="flex items-center gap-3 mb-2">
                <i data-lucide="{{ $icon }}" class="w-4 h-4 {{ $color }}"></i>
                <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest">{{ $label }}</span>
            </div>
            <div class="text-3xl font-black {{ $color }}">{{ $val }}</div>
        </div>
        @endforeach
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-sn-card p-5 rounded-2xl">
            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Daily Ticket Trend</h3>
            <div id="dailyTrend" class="h-64"></div>
        </div>
        <div class="bg-sn-card p-5 rounded-2xl">
            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">By Priority</h3>
            <div id="priorityChart" class="h-64"></div>
        </div>
    </div>

    {{-- Agent Performance --}}
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5">
            <h3 class="text-sm font-bold">Agent Performance</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5">
                        <th class="px-5 py-3">Agent</th>
                        <th class="px-5 py-3">Resolved</th>
                        <th class="px-5 py-3">Avg Resolution</th>
                        <th class="px-5 py-3">Currently Open</th>
                        <th class="px-5 py-3">Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agentPerformance as $agent)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold text-[10px]">
                                    {{ strtoupper(substr($agent['name'], 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-white">{{ $agent['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-sm font-bold text-green-400">{{ $agent['resolved'] }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $agent['avg_hours'] ? $agent['avg_hours'].'h' : '—' }}</td>
                        <td class="px-5 py-3 text-xs text-blue-400">{{ $agent['open'] }}</td>
                        <td class="px-5 py-3">
                            <div class="w-24 bg-white/5 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-primary h-full rounded-full" style="width: {{ $agentPerformance->max('resolved') > 0 ? min(100, ($agent['resolved'] / $agentPerformance->max('resolved')) * 100) : 0 }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-600 text-sm">No data for selected period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- By Category --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-sn-card rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-white/5"><h3 class="text-sm font-bold">Top Categories</h3></div>
            <div class="p-5 space-y-3">
                @forelse($byCategory as $cat)
                <div>
                    <div class="flex justify-between text-[10px] font-black uppercase mb-1.5">
                        <span class="text-gray-400">{{ $cat->category ?? 'Uncategorized' }}</span>
                        <span class="text-white">{{ $cat->count }}</span>
                    </div>
                    <div class="w-full bg-white/5 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-primary h-full rounded-full" style="width: {{ $byCategory->max('count') > 0 ? min(100, ($cat->count / $byCategory->max('count')) * 100) : 0 }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-600 text-center py-4">No data</p>
                @endforelse
            </div>
        </div>
        <div class="bg-sn-card p-5 rounded-2xl">
            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">By Status</h3>
            <div id="statusChart" class="h-56"></div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const chartBase = { chart: { toolbar: { show: false }, background: 'transparent', fontFamily: 'Inter, sans-serif' }, theme: { mode: 'dark' }, grid: { borderColor: 'rgba(255,255,255,0.05)' } };

new ApexCharts(document.querySelector('#dailyTrend'), {
    ...chartBase,
    series: [
        { name: 'Created',  data: @json($dailyTrend->pluck('created')) },
        { name: 'Resolved', data: @json($dailyTrend->pluck('resolved')) }
    ],
    chart: { ...chartBase.chart, type: 'bar', height: '100%', stacked: false },
    colors: ['#3b82f6', '#81B532'],
    xaxis: { categories: @json($dailyTrend->pluck('date')), labels: { style: { colors: '#6b7280', fontSize: '10px' } } },
    yaxis: { labels: { style: { colors: '#6b7280', fontSize: '10px' } } },
    legend: { labels: { colors: '#9ca3af' } },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
    dataLabels: { enabled: false },
}).render();

new ApexCharts(document.querySelector('#priorityChart'), {
    ...chartBase,
    series: @json($byPriority->pluck('count')),
    chart: { ...chartBase.chart, type: 'donut', height: '100%' },
    labels: @json($byPriority->map(fn($i) => is_object($i->priority) ? $i->priority->value : ($i->priority ?? 'Unknown'))),
    colors: ['#ef4444', '#f59e0b', '#81B532', '#3b82f6'],
    plotOptions: { pie: { donut: { size: '70%' } } },
    dataLabels: { enabled: false },
    legend: { position: 'bottom', labels: { colors: '#9ca3af' }, fontSize: '10px' },
}).render();

new ApexCharts(document.querySelector('#statusChart'), {
    ...chartBase,
    series: @json($byStatus->pluck('count')),
    chart: { ...chartBase.chart, type: 'pie', height: '100%' },
    labels: @json($byStatus->map(fn($i) => is_object($i->status) ? $i->status->value : ($i->status ?? 'Unknown'))),
    colors: ['#3b82f6', '#6366f1', '#f59e0b', '#f97316', '#81B532', '#6b7280', '#ef4444'],
    dataLabels: { enabled: false },
    legend: { position: 'bottom', labels: { colors: '#9ca3af' }, fontSize: '10px' },
}).render();
</script>
@endpush
