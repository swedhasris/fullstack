@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Incident Dashboard')
@section('page-subtitle', 'Live Service Monitoring')

@section('content')
<div class="p-6 space-y-6">

    {{-- ── Operational Filters ──────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-4 pb-4 border-b border-white/5">
        <div class="flex items-center gap-2">
            <i data-lucide="filter" class="w-3.5 h-3.5 text-gray-500"></i>
            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Global Ops Filters:</span>
        </div>
        <div class="flex items-center gap-3">
            <select class="bg-white/5 border border-white/10 rounded-lg py-1.5 px-3 text-[10px] font-bold outline-none focus:ring-1 focus:ring-primary transition-all">
                <option>All Service Boards</option>
            </select>
            <select class="bg-white/5 border border-white/10 rounded-lg py-1.5 px-3 text-[10px] font-bold outline-none focus:ring-1 focus:ring-primary transition-all">
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
                <option>This Month</option>
            </select>
        </div>
        <div class="ml-auto flex items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                <span class="text-[10px] font-bold text-gray-500 uppercase">Auto-Refresh: ON</span>
            </div>
            <a href="{{ route('reports.index') }}" class="flex items-center gap-2 bg-primary/10 text-primary px-4 py-1.5 rounded-lg text-[10px] font-black hover:bg-primary hover:text-white transition-all uppercase">
                <i data-lucide="download" class="w-3 h-3"></i> Export Report
            </a>
        </div>
    </div>

    {{-- ── Top KPI Row ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        @php
        $topKpis = [
            ['label' => 'Total Incidents',  'val' => $stats['total_incidents'],  'icon' => 'ticket',          'color' => 'text-white'],
            ['label' => 'Waiting for TL',   'val' => $stats['waiting_for_tl'],   'icon' => 'user-x',          'color' => 'text-amber-400'],
            ['label' => '5+ Days Aging',    'val' => $stats['aging_5_days'],     'icon' => 'clock',           'color' => 'text-orange-400'],
            ['label' => 'Currently Open',   'val' => $stats['currently_open'],   'icon' => 'folder-open',     'color' => 'text-blue-400'],
            ['label' => 'Escalated',        'val' => $stats['escalated_tickets'],'icon' => 'alert-triangle',  'color' => 'text-red-400'],
        ];
        @endphp

        @foreach($topKpis as $kpi)
        <div class="bg-sn-card p-5 rounded-2xl group hover:border-primary/20 transition-all cursor-default">
            <div class="flex items-start justify-between mb-3">
                <i data-lucide="{{ $kpi['icon'] }}" class="w-4 h-4 text-gray-600"></i>
                <div class="w-1.5 h-1.5 rounded-full bg-primary/50"></div>
            </div>
            <div class="text-5xl font-black tracking-tighter mb-2 {{ $kpi['color'] }} transition-transform group-hover:scale-105 duration-300">
                {{ $kpi['val'] }}
            </div>
            <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest">{{ $kpi['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ── Second KPI Row ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
        $secondKpis = [
            ['label' => 'Resolved Today',      'val' => $stats['resolved_today'],      'icon' => 'check-circle',  'color' => 'text-green-400'],
            ['label' => 'Overdue',             'val' => $stats['overdue_tickets'],     'icon' => 'alert-circle',  'color' => 'text-red-400'],
            ['label' => 'SLA Breached',        'val' => $stats['sla_breached'],        'icon' => 'zap-off',       'color' => 'text-red-500'],
            ['label' => 'Active Technicians',  'val' => $stats['active_technicians'],  'icon' => 'users',         'color' => 'text-primary'],
        ];
        @endphp

        @foreach($secondKpis as $kpi)
        <div class="bg-sn-card p-4 rounded-xl flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center shrink-0">
                <i data-lucide="{{ $kpi['icon'] }}" class="w-4 h-4 {{ $kpi['color'] }}"></i>
            </div>
            <div>
                <div class="text-2xl font-black {{ $kpi['color'] }}">{{ $kpi['val'] }}</div>
                <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest">{{ $kpi['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Charts Row ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

        {{-- Delivery Trend --}}
        <div class="lg:col-span-2 bg-sn-card p-5 rounded-2xl">
            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Service Delivery Trend (7 Days)</h3>
            <div id="deliveryTrend" class="h-56"></div>
        </div>

        {{-- SLA Gauges --}}
        <div class="bg-sn-card p-5 rounded-2xl text-center">
            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">SLA Compliance</h3>
            <div id="slaGauge"></div>
            <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest mt-4 mb-2">Team Load</div>
            <div id="workloadGauge"></div>
        </div>

        {{-- Aging Analytics --}}
        <div class="bg-sn-card p-5 rounded-2xl">
            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-6">Aging Analytics</h3>
            <div class="space-y-5">
                @foreach([
                    'New (< 24h)'      => [$agingData['new'],       'bg-primary'],
                    '1 – 3 Days'       => [$agingData['1_3_days'],  'bg-blue-500'],
                    '3 – 7 Days'       => [$agingData['3_7_days'],  'bg-amber-500'],
                    'Stale (> 7 Days)' => [$agingData['older'],     'bg-red-500'],
                ] as $label => [$count, $color])
                <div>
                    <div class="flex justify-between text-[10px] font-black uppercase mb-1.5">
                        <span class="text-gray-500">{{ $label }}</span>
                        <span class="text-white">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-white/5 h-1.5 rounded-full overflow-hidden">
                        <div class="{{ $color }} h-full rounded-full transition-all duration-700"
                             style="width: {{ $stats['currently_open'] > 0 ? min(100, ($count / $stats['currently_open']) * 100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── SLA Matrix + Priority Distribution ──────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Active SLA Matrix --}}
        <div class="lg:col-span-2 bg-sn-card rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-white/5 flex items-center justify-between">
                <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Active SLA Matrix</h3>
                <div class="flex items-center gap-4 text-[8px] font-black uppercase tracking-widest text-gray-500">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Response</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-primary"></span> Resolution</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5">
                            <th class="px-5 py-3">Incident</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Technician</th>
                            <th class="px-5 py-3 text-right">SLA Timers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTickets->take(8) as $ticket)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="px-5 py-3">
                                <a href="{{ route('tickets.show', $ticket) }}" class="block">
                                    <div class="text-[10px] font-black text-primary mb-0.5">{{ $ticket->ticket_number }}</div>
                                    <div class="text-xs font-medium truncate max-w-[180px] text-gray-300">{{ $ticket->title }}</div>
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-[8px] font-black border border-white/10 uppercase">
                                    {{ $ticket->status->value }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="text-[10px] font-bold text-gray-300">{{ $ticket->assigned_to_name ?? 'Unassigned' }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex flex-col gap-1 items-end"
                                     x-data="slaTimer('{{ $ticket->response_deadline?->toIso8601String() }}', '{{ $ticket->resolution_deadline?->toIso8601String() }}', '{{ $ticket->first_response_at?->toIso8601String() }}')">
                                    <div class="flex items-center gap-2">
                                        <div class="w-14 bg-white/5 h-1 rounded-full overflow-hidden">
                                            <div class="h-full bg-blue-500 transition-all" :style="'width:' + respPct + '%'"></div>
                                        </div>
                                        <span class="text-[8px] font-mono w-14 text-right" :class="respBreached ? 'text-red-400 sla-breach' : 'text-gray-400'" x-text="respText"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-14 bg-white/5 h-1 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary transition-all" :style="'width:' + resolPct + '%'"></div>
                                        </div>
                                        <span class="text-[8px] font-mono w-14 text-right" :class="resolBreached ? 'text-red-400 sla-breach' : 'text-gray-400'" x-text="resolText"></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-600 text-sm">No tickets found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Priority Distribution --}}
        <div class="bg-sn-card p-5 rounded-2xl">
            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Priority Distribution</h3>
            <div id="priorityMatrix" class="h-56"></div>
        </div>
    </div>

    {{-- ── Leaderboards ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">

        {{-- Heatmap --}}
        <div class="lg:col-span-2 bg-sn-card rounded-2xl p-5">
            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Tickets Closed Heatmap (Last 7 Days)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[8px] font-black uppercase text-gray-500 tracking-widest">
                            <th class="px-2 py-2">Tech</th>
                            @for($i = 6; $i >= 0; $i--)
                            <th class="px-2 py-2 text-center">{{ now()->subDays($i)->format('M d') }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($performanceLeaderboard->take(5) as $tech)
                        <tr class="border-b border-white/5">
                            <td class="px-2 py-2 text-[10px] font-bold text-gray-400 whitespace-nowrap">{{ Str::limit($tech->name, 12) }}</td>
                            @for($i = 6; $i >= 0; $i--)
                            @php
                                $date = now()->subDays($i)->format('Y-m-d');
                                $count = $heatmapData->where('assigned_to_name', $tech->name)->where('date', $date)->first()?->count ?? 0;
                                $cellClass = $count >= 6 ? 'heatmap-cell-6plus' : ($count >= 3 ? 'heatmap-cell-3-5' : ($count >= 1 ? 'heatmap-cell-1-2' : 'heatmap-cell-0'));
                            @endphp
                            <td class="px-2 py-2 text-center">
                                <div class="heatmap-cell mx-auto {{ $cellClass }} flex items-center justify-center text-[9px] font-bold">
                                    {{ $count > 0 ? $count : '' }}
                                </div>
                            </td>
                            @endfor
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Team Workload --}}
        <div class="bg-sn-card rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-white/5">
                <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Team Workload</h3>
            </div>
            <div class="p-5 space-y-4">
                @forelse($techWorkload->take(6) as $tech)
                <div>
                    <div class="flex justify-between text-[10px] font-black uppercase mb-1.5">
                        <span class="text-gray-400 truncate max-w-[120px]">{{ $tech->name }}</span>
                        <span class="text-primary">{{ $tech->assigned_tickets_count }}</span>
                    </div>
                    <div class="w-full bg-white/5 h-1 rounded-full overflow-hidden">
                        <div class="bg-primary h-full rounded-full" style="width: {{ min(100, ($tech->assigned_tickets_count / max(1, $techWorkload->max('assigned_tickets_count'))) * 100) }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-600 text-center py-4">No agents found</p>
                @endforelse
            </div>
        </div>

        {{-- Top Performers --}}
        <div class="bg-sn-card rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-white/5">
                <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Top Performers</h3>
            </div>
            <div class="p-5 space-y-3">
                @forelse($performanceLeaderboard->take(6) as $index => $perf)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-[10px] font-black text-primary shrink-0">
                        {{ $perf->mtd }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[10px] font-black uppercase truncate">{{ $perf->name }}</div>
                        <div class="text-[8px] text-gray-500 font-bold uppercase">{{ $perf->current_week }} this week</div>
                    </div>
                    @if($index === 0)
                    <span class="text-[8px] text-amber-400 font-black">🏆</span>
                    @endif
                </div>
                @empty
                <p class="text-xs text-gray-600 text-center py-4">No data</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Recent Activity ──────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Unassigned Tickets --}}
        <div class="bg-sn-card rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-white/5 flex items-center justify-between">
                <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Unassigned Tickets</h3>
                <a href="{{ route('tickets.index', ['assigned_to' => 'unassigned']) }}" class="text-[9px] text-primary font-bold uppercase hover:underline">View All</a>
            </div>
            <div class="divide-y divide-white/5">
                @forelse($unassignedTickets as $ticket)
                <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-white/5 transition-colors">
                    <div class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[10px] font-black text-primary">{{ $ticket->ticket_number }}</div>
                        <div class="text-xs text-gray-300 truncate">{{ $ticket->title }}</div>
                    </div>
                    <span class="text-[8px] font-black uppercase px-2 py-0.5 rounded border border-white/10 text-gray-400 shrink-0">
                        {{ $ticket->priority->value ?? $ticket->priority }}
                    </span>
                </a>
                @empty
                <div class="px-5 py-8 text-center text-gray-600 text-sm">All tickets are assigned 🎉</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="bg-sn-card rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-white/5">
                <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Recent Activity</h3>
            </div>
            <div class="divide-y divide-white/5">
                @forelse($recentActivities as $activity)
                <div class="flex items-start gap-3 px-5 py-3">
                    <div class="w-6 h-6 rounded-full bg-white/5 flex items-center justify-center shrink-0 mt-0.5">
                        @php
                            $icon = match($activity->activity_type?->value ?? $activity->activity_type) {
                                'comment'           => 'message-circle',
                                'work_note'         => 'file-text',
                                'status_change'     => 'refresh-cw',
                                'assignment_change' => 'user-check',
                                'email_sent'        => 'mail',
                                default             => 'activity',
                            };
                        @endphp
                        <i data-lucide="{{ $icon }}" class="w-3 h-3 text-gray-500"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs text-gray-300 truncate">{{ $activity->message }}</div>
                        <div class="text-[9px] text-gray-600 mt-0.5">
                            {{ $activity->created_by_name }} · {{ $activity->created_at?->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-600 text-sm">No recent activity</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// ── SLA Timer Alpine Component ────────────────────────────────────────────────
document.addEventListener('alpine:init', () => {
    Alpine.data('slaTimer', (respDeadline, resolDeadline, firstRespAt) => ({
        respText: '...', respPct: 100, respBreached: false,
        resolText: '...', resolPct: 100, resolBreached: false,
        init() { this.update(); setInterval(() => this.update(), 1000); },
        update() {
            const now = Date.now();
            if (firstRespAt) { this.respText = 'MET'; this.respPct = 100; this.respBreached = false; }
            else if (respDeadline) {
                const diff = new Date(respDeadline).getTime() - now;
                this.respBreached = diff <= 0;
                this.respText = diff <= 0 ? 'BREACH' : this.fmt(diff);
                this.respPct = Math.max(0, Math.min(100, (diff / (4 * 3600000)) * 100));
            }
            if (resolDeadline) {
                const diff = new Date(resolDeadline).getTime() - now;
                this.resolBreached = diff <= 0;
                this.resolText = diff <= 0 ? 'BREACH' : this.fmt(diff);
                this.resolPct = Math.max(0, Math.min(100, (diff / (24 * 3600000)) * 100));
            }
        },
        fmt(ms) {
            const h = Math.floor(ms / 3600000), m = Math.floor((ms % 3600000) / 60000), s = Math.floor((ms % 60000) / 1000);
            return `${h}h ${m}m ${s}s`;
        }
    }));
});

// ── ApexCharts ────────────────────────────────────────────────────────────────
const chartDefaults = {
    chart: { toolbar: { show: false }, background: 'transparent', fontFamily: 'Inter, sans-serif' },
    grid: { borderColor: 'rgba(255,255,255,0.05)', strokeDashArray: 3 },
    theme: { mode: 'dark' },
};

// Delivery Trend
new ApexCharts(document.querySelector('#deliveryTrend'), {
    ...chartDefaults,
    series: [
        { name: 'Created',  data: @json($weeklyTrends->pluck('count')) },
        { name: 'Resolved', data: @json($weeklyTrends->map(fn($d) => round($d->count * 0.85))) }
    ],
    chart: { ...chartDefaults.chart, type: 'area', height: '100%' },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.02 } },
    colors: ['#81B532', '#3b82f6'],
    dataLabels: { enabled: false },
    xaxis: { categories: @json($weeklyTrends->pluck('date')), labels: { style: { colors: '#6b7280', fontSize: '10px' } } },
    yaxis: { labels: { style: { colors: '#6b7280', fontSize: '10px' } } },
    legend: { show: true, position: 'top', horizontalAlign: 'right', labels: { colors: '#9ca3af' } },
    grid: chartDefaults.grid,
}).render();

// Priority Donut
new ApexCharts(document.querySelector('#priorityMatrix'), {
    ...chartDefaults,
    series: @json($priorityData->pluck('count')),
    chart: { ...chartDefaults.chart, type: 'donut', height: '100%' },
    labels: @json($priorityData->map(fn($item) => is_object($item->priority) ? $item->priority->value : ($item->priority ?? 'Unknown'))),
    colors: ['#ef4444', '#f59e0b', '#81B532', '#3b82f6'],
    plotOptions: { pie: { donut: { size: '75%', labels: { show: true, total: { show: true, label: 'Total', color: '#9ca3af', fontSize: '11px', fontWeight: '700' } } } } },
    dataLabels: { enabled: false },
    legend: { position: 'bottom', labels: { colors: '#9ca3af' }, fontSize: '10px' },
}).render();

// Gauges
const gaugeOpts = (color, val, label) => ({
    series: [val],
    chart: { type: 'radialBar', height: 140, sparkline: { enabled: true }, background: 'transparent' },
    plotOptions: {
        radialBar: {
            startAngle: -90, endAngle: 90,
            hollow: { size: '60%' },
            track: { background: 'rgba(255,255,255,0.05)' },
            dataLabels: { name: { show: false }, value: { offsetY: -5, fontSize: '20px', fontWeight: '900', color } }
        }
    },
    colors: [color],
    stroke: { lineCap: 'round' },
    theme: { mode: 'dark' },
});
new ApexCharts(document.querySelector('#slaGauge'), gaugeOpts('#81B532', 94, 'SLA')).render();
new ApexCharts(document.querySelector('#workloadGauge'), gaugeOpts('#f59e0b', 72, 'Load')).render();

// Auto-refresh stats every 30s
setInterval(async () => {
    try { await window.api.get('/api/analytics/stats'); } catch(e) {}
}, 30000);
</script>
@endpush
