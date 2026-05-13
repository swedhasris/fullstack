@extends('layouts.app')

@section('title', 'Incidents')
@section('page-title', 'Incident Management')
@section('page-subtitle', 'All Tickets · Service Desk')

@section('content')
<div class="p-6 space-y-4">

    {{-- ── Stats Bar ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach([
            ['Open',     $stats['open'],     'folder-open',    'text-blue-400'],
            ['Critical', $stats['critical'], 'alert-triangle', 'text-red-400'],
            ['Overdue',  $stats['overdue'],  'clock',          'text-orange-400'],
            ['My Open',  $stats['my_open'],  'user',           'text-primary'],
        ] as [$label, $val, $icon, $color])
        <div class="bg-sn-card p-4 rounded-xl flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center shrink-0">
                <i data-lucide="{{ $icon }}" class="w-4 h-4 {{ $color }}"></i>
            </div>
            <div>
                <div class="text-xl font-black {{ $color }}">{{ $val }}</div>
                <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest">{{ $label }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
    <div class="bg-sn-card rounded-2xl p-4">
        <form method="GET" action="{{ route('tickets.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Search</label>
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-500"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ticket #, title, caller..."
                           class="w-full bg-white/5 border border-white/10 rounded-xl pl-9 pr-4 py-2 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600">
                </div>
            </div>

            <div class="min-w-[140px]">
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Status</label>
                <select name="status" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[140px]">
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Priority</label>
                <select name="priority" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                    <option value="">All Priorities</option>
                    @foreach($priorities as $priority)
                    <option value="{{ $priority->value }}" {{ request('priority') === $priority->value ? 'selected' : '' }}>{{ $priority->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[160px]">
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Assigned To</label>
                <select name="assigned_to" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                    <option value="">All Agents</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->uid }}" {{ request('assigned_to') === $agent->uid ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter
                </button>
                <a href="{{ route('tickets.index') }}" class="flex items-center gap-2 bg-white/5 text-gray-400 px-4 py-2 rounded-xl text-xs font-bold hover:bg-white/10 transition-colors">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Clear
                </a>
            </div>
        </form>
    </div>

    {{-- ── Ticket Table ─────────────────────────────────────────────────────── --}}
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold">Tickets</h3>
                <p class="text-[9px] text-gray-500 font-bold uppercase tracking-widest mt-0.5">{{ $tickets->total() }} total results</p>
            </div>
            <a href="{{ route('tickets.create') }}" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> New Ticket
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5 bg-white/2">
                        <th class="px-5 py-3">Ticket #</th>
                        <th class="px-5 py-3">Title</th>
                        <th class="px-5 py-3">Caller</th>
                        <th class="px-5 py-3">Priority</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Assigned To</th>
                        <th class="px-5 py-3">SLA</th>
                        <th class="px-5 py-3">Created</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors group">
                        <td class="px-5 py-3">
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-[10px] font-black text-primary hover:underline">
                                {{ $ticket->ticket_number }}
                            </a>
                        </td>
                        <td class="px-5 py-3 max-w-[220px]">
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-sm font-medium text-gray-200 hover:text-white truncate block">
                                {{ $ticket->title }}
                            </a>
                            @if($ticket->category)
                            <div class="text-[9px] text-gray-600 mt-0.5">{{ $ticket->category }}{{ $ticket->subcategory ? ' › ' . $ticket->subcategory : '' }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-xs text-gray-300">{{ $ticket->caller }}</div>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $priorityColors = ['1 - Critical' => 'bg-red-500/20 text-red-400 border-red-500/30', '2 - High' => 'bg-orange-500/20 text-orange-400 border-orange-500/30', '3 - Moderate' => 'bg-amber-500/20 text-amber-400 border-amber-500/30', '4 - Low' => 'bg-blue-500/20 text-blue-400 border-blue-500/30'];
                                $pVal = is_object($ticket->priority) ? $ticket->priority->value : $ticket->priority;
                                $pColor = $priorityColors[$pVal] ?? 'bg-gray-500/20 text-gray-400 border-gray-500/30';
                            @endphp
                            <span class="px-2 py-0.5 rounded-lg text-[8px] font-black border uppercase {{ $pColor }}">
                                {{ $pVal }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $statusColors = ['New' => 'bg-blue-500/20 text-blue-400', 'Assigned' => 'bg-indigo-500/20 text-indigo-400', 'In Progress' => 'bg-yellow-500/20 text-yellow-400', 'On Hold' => 'bg-orange-500/20 text-orange-400', 'Resolved' => 'bg-green-500/20 text-green-400', 'Closed' => 'bg-gray-500/20 text-gray-400', 'Canceled' => 'bg-red-500/20 text-red-400'];
                                $sVal = is_object($ticket->status) ? $ticket->status->value : $ticket->status;
                                $sColor = $statusColors[$sVal] ?? 'bg-gray-500/20 text-gray-400';
                            @endphp
                            <span class="px-2 py-0.5 rounded-lg text-[8px] font-black uppercase {{ $sColor }}">
                                {{ $sVal }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-xs text-gray-300">{{ $ticket->assigned_to_name ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-3">
                            @if($ticket->resolution_deadline)
                            <div x-data="slaCountdown('{{ $ticket->resolution_deadline->toIso8601String() }}')" class="text-[9px] font-mono" :class="breached ? 'text-red-400 sla-breach' : 'text-gray-400'" x-text="text"></div>
                            @else
                            <span class="text-[9px] text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-[9px] text-gray-500">{{ $ticket->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('tickets.show', $ticket) }}" class="opacity-0 group-hover:opacity-100 transition-opacity p-1.5 hover:bg-white/10 rounded-lg inline-flex">
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-gray-400"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center">
                            <i data-lucide="inbox" class="w-8 h-8 text-gray-700 mx-auto mb-3"></i>
                            <p class="text-gray-500 text-sm">No tickets found</p>
                            <a href="{{ route('tickets.create') }}" class="mt-3 inline-flex items-center gap-2 text-primary text-xs font-bold hover:underline">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Create first ticket
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tickets->hasPages())
        <div class="p-4 border-t border-white/5">
            {{ $tickets->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('slaCountdown', (deadline) => ({
        text: '...', breached: false,
        init() { this.update(); setInterval(() => this.update(), 1000); },
        update() {
            const diff = new Date(deadline).getTime() - Date.now();
            this.breached = diff <= 0;
            if (diff <= 0) { this.text = 'BREACHED'; return; }
            const h = Math.floor(diff / 3600000), m = Math.floor((diff % 3600000) / 60000);
            this.text = `${h}h ${m}m`;
        }
    }));
});
</script>
@endpush
