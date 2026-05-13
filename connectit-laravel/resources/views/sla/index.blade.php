@extends('layouts.app')
@section('title', 'SLA Management')
@section('page-title', 'SLA Management')
@section('page-subtitle', 'Service Level Agreements')

@section('content')
<div class="p-6 space-y-5" x-data="{ showCreate: false }">

    {{-- Metrics --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        @foreach([
            ['Total Open',      $metrics['total_open'],      'folder-open',   'text-blue-400'],
            ['SLA Breached',    $metrics['sla_breached'],    'zap-off',       'text-red-400'],
            ['At Risk (< 2h)',  $metrics['sla_at_risk'],     'alert-triangle','text-orange-400'],
            ['Healthy',         $metrics['sla_healthy'],     'check-circle',  'text-green-400'],
            ['Compliance Rate', $metrics['compliance_rate'].'%', 'trending-up', 'text-primary'],
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

    {{-- SLA Policies --}}
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5 flex items-center justify-between">
            <h3 class="text-sm font-bold">SLA Policies</h3>
            <button @click="showCreate = !showCreate" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Policy
            </button>
        </div>

        {{-- Create Form --}}
        <div x-show="showCreate" x-transition class="p-5 border-b border-white/5 bg-white/2">
            <form method="POST" action="{{ route('sla.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Policy Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Critical SLA"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Priority *</label>
                    <select name="priority" required class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                        <option value="1 - Critical">1 - Critical</option>
                        <option value="2 - High">2 - High</option>
                        <option value="3 - Moderate">3 - Moderate</option>
                        <option value="4 - Low">4 - Low</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Category</label>
                    <input type="text" name="category" placeholder="Leave blank for all"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Response Time (hours) *</label>
                    <input type="number" name="response_time_hours" required min="1" value="4"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Resolution Time (hours) *</label>
                    <input type="number" name="resolution_time_hours" required min="1" value="24"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
                        Create Policy
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5">
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Priority</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Response</th>
                        <th class="px-5 py-3">Resolution</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($policies as $policy)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="px-5 py-3 text-sm font-medium text-white">{{ $policy->name }}</td>
                        <td class="px-5 py-3">
                            <span class="text-[9px] font-black px-2 py-0.5 rounded bg-white/5 text-gray-300">{{ $policy->priority }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $policy->category ?? 'All' }}</td>
                        <td class="px-5 py-3 text-xs text-blue-400 font-bold">{{ $policy->response_time_hours }}h</td>
                        <td class="px-5 py-3 text-xs text-primary font-bold">{{ $policy->resolution_time_hours }}h</td>
                        <td class="px-5 py-3">
                            <span class="text-[9px] font-black px-2 py-0.5 rounded {{ $policy->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                                {{ $policy->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('sla.destroy', $policy) }}" onsubmit="return confirm('Delete this SLA policy?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 hover:bg-red-500/10 rounded-lg transition-colors">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-400"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-600 text-sm">No SLA policies configured</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Breached Tickets --}}
    @if($breachedTickets->count())
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5">
            <h3 class="text-sm font-bold text-red-400">⚠ SLA Breached Tickets ({{ $breachedTickets->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5">
                        <th class="px-5 py-3">Ticket</th>
                        <th class="px-5 py-3">Priority</th>
                        <th class="px-5 py-3">Assigned To</th>
                        <th class="px-5 py-3">Deadline</th>
                        <th class="px-5 py-3">Overdue By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($breachedTickets as $ticket)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-[10px] font-black text-primary hover:underline">{{ $ticket->ticket_number }}</a>
                            <div class="text-xs text-gray-400 truncate max-w-[200px]">{{ $ticket->title }}</div>
                        </td>
                        <td class="px-5 py-3"><span class="text-[9px] font-black text-red-400">{{ is_object($ticket->priority) ? $ticket->priority->value : $ticket->priority }}</span></td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $ticket->assigned_to_name ?? 'Unassigned' }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $ticket->resolution_deadline->format('M d, H:i') }}</td>
                        <td class="px-5 py-3 text-xs text-red-400 font-bold">{{ $ticket->resolution_deadline->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
