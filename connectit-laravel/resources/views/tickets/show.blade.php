@extends('layouts.app')

@section('title', $ticket->ticket_number)
@section('page-title', $ticket->ticket_number)
@section('page-subtitle', 'Ticket Detail · ' . (is_object($ticket->status) ? $ticket->status->value : $ticket->status))

@section('content')
<div class="p-6" x-data="ticketDetail()">

    {{-- ── Breadcrumb ───────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-5">
        <a href="{{ route('tickets.index') }}" class="hover:text-primary transition-colors">Incidents</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-primary">{{ $ticket->ticket_number }}</span>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- ── Left: Ticket Info ────────────────────────────────────────────── --}}
        <div class="xl:col-span-2 space-y-4">

            {{-- Header Card --}}
            <div class="bg-sn-card rounded-2xl p-5">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-white leading-snug">{{ $ticket->title }}</h2>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            @php
                                $pVal = is_object($ticket->priority) ? $ticket->priority->value : $ticket->priority;
                                $sVal = is_object($ticket->status) ? $ticket->status->value : $ticket->status;
                                $priorityColors = ['1 - Critical' => 'bg-red-500/20 text-red-400 border-red-500/30', '2 - High' => 'bg-orange-500/20 text-orange-400 border-orange-500/30', '3 - Moderate' => 'bg-amber-500/20 text-amber-400 border-amber-500/30', '4 - Low' => 'bg-blue-500/20 text-blue-400 border-blue-500/30'];
                                $statusColors = ['New' => 'bg-blue-500/20 text-blue-400', 'Assigned' => 'bg-indigo-500/20 text-indigo-400', 'In Progress' => 'bg-yellow-500/20 text-yellow-400', 'On Hold' => 'bg-orange-500/20 text-orange-400', 'Resolved' => 'bg-green-500/20 text-green-400', 'Closed' => 'bg-gray-500/20 text-gray-400'];
                            @endphp
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black border uppercase {{ $priorityColors[$pVal] ?? 'bg-gray-500/20 text-gray-400 border-gray-500/30' }}">
                                {{ $pVal }}
                            </span>
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase {{ $statusColors[$sVal] ?? 'bg-gray-500/20 text-gray-400' }}">
                                {{ $sVal }}
                            </span>
                            @if($ticket->category)
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black bg-white/5 text-gray-400">
                                {{ $ticket->category }}{{ $ticket->subcategory ? ' › ' . $ticket->subcategory : '' }}
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- SLA Timers --}}
                    @if($ticket->resolution_deadline && !$ticket->isResolved())
                    <div class="shrink-0 text-right" x-data="slaTimerDetail('{{ $ticket->response_deadline?->toIso8601String() }}', '{{ $ticket->resolution_deadline->toIso8601String() }}', '{{ $ticket->first_response_at?->toIso8601String() }}')">
                        <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1">Response SLA</div>
                        <div class="text-sm font-black font-mono" :class="respBreached ? 'text-red-400 sla-breach' : 'text-blue-400'" x-text="respText"></div>
                        <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mt-2 mb-1">Resolution SLA</div>
                        <div class="text-sm font-black font-mono" :class="resolBreached ? 'text-red-400 sla-breach' : 'text-primary'" x-text="resolText"></div>
                    </div>
                    @endif
                </div>

                @if($ticket->description)
                <div class="bg-white/3 rounded-xl p-4 mt-3">
                    <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Description</div>
                    <p class="text-sm text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $ticket->description }}</p>
                </div>
                @endif
            </div>

            {{-- ── Activity Timeline ─────────────────────────────────────────── --}}
            <div class="bg-sn-card rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-white/5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold">Activity Timeline</h3>
                        <div class="flex gap-1" x-data="{ tab: 'all' }">
                            @foreach(['all' => 'All', 'comment' => 'Comments', 'work_note' => 'Work Notes', 'system' => 'System'] as $key => $label)
                            <button @click="tab = '{{ $key }}'; filterActivities('{{ $key }}')"
                                    :class="tab === '{{ $key }}' ? 'bg-primary/10 text-primary border-primary/20' : 'bg-white/5 text-gray-500 border-white/10'"
                                    class="px-3 py-1 rounded-lg text-[9px] font-black uppercase border transition-all">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Activities --}}
                <div class="divide-y divide-white/5 max-h-[500px] overflow-y-auto custom-scrollbar" id="activityFeed">
                    @forelse($ticket->activities as $activity)
                    @php
                        $aType = is_object($activity->activity_type) ? $activity->activity_type->value : $activity->activity_type;
                        $isInternal = (is_object($activity->visibility_type) ? $activity->visibility_type->value : $activity->visibility_type) === 'internal';
                        $icons = ['comment' => 'message-circle', 'work_note' => 'file-text', 'status_change' => 'refresh-cw', 'assignment_change' => 'user-check', 'email_sent' => 'mail', 'whatsapp_sent' => 'message-square', 'system' => 'zap', 'resolution' => 'check-circle'];
                        $icon = $icons[$aType] ?? 'activity';
                        $colors = ['comment' => 'bg-blue-500/20 text-blue-400', 'work_note' => 'bg-purple-500/20 text-purple-400', 'status_change' => 'bg-yellow-500/20 text-yellow-400', 'assignment_change' => 'bg-indigo-500/20 text-indigo-400', 'email_sent' => 'bg-green-500/20 text-green-400', 'system' => 'bg-gray-500/20 text-gray-400', 'resolution' => 'bg-green-500/20 text-green-400'];
                        $color = $colors[$aType] ?? 'bg-gray-500/20 text-gray-400';
                    @endphp
                    <div class="flex gap-4 px-5 py-4 activity-item" data-type="{{ $aType }}" data-visibility="{{ is_object($activity->visibility_type) ? $activity->visibility_type->value : $activity->visibility_type }}">
                        <div class="w-8 h-8 rounded-xl {{ $color }} flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-bold text-white">{{ $activity->created_by_name ?? 'System' }}</span>
                                @if($isInternal)
                                <span class="px-1.5 py-0.5 rounded text-[8px] font-black bg-purple-500/20 text-purple-400 uppercase">Internal</span>
                                @endif
                                <span class="text-[9px] text-gray-600 ml-auto">{{ $activity->created_at?->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-300 leading-relaxed">{{ $activity->message }}</p>
                            @if($activity->metadata_json)
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($activity->metadata_json as $key => $val)
                                @if($val && !is_array($val))
                                <span class="text-[8px] bg-white/5 text-gray-500 px-2 py-0.5 rounded font-mono">{{ $key }}: {{ $val }}</span>
                                @endif
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-8 text-center text-gray-600 text-sm">No activity yet</div>
                    @endforelse
                </div>

                {{-- Add Comment --}}
                @if(auth()->user()->canManageTickets() || $ticket->caller_user_id === auth()->user()->uid)
                <div class="p-5 border-t border-white/5" x-data="{ isInternal: false, message: '', loading: false }">
                    <div class="flex gap-2 mb-3">
                        <button @click="isInternal = false" :class="!isInternal ? 'bg-blue-500/20 text-blue-400 border-blue-500/30' : 'bg-white/5 text-gray-500 border-white/10'"
                                class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase border transition-all">
                            <i data-lucide="message-circle" class="w-3 h-3 inline mr-1"></i> Public Comment
                        </button>
                        @if(auth()->user()->canManageTickets())
                        <button @click="isInternal = true" :class="isInternal ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' : 'bg-white/5 text-gray-500 border-white/10'"
                                class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase border transition-all">
                            <i data-lucide="file-text" class="w-3 h-3 inline mr-1"></i> Work Note
                        </button>
                        @endif
                    </div>
                    <textarea x-model="message" rows="3" placeholder="Add a comment or work note..."
                              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600 resize-none"></textarea>
                    <div class="flex justify-end mt-2">
                        <button @click="submitComment()" :disabled="loading || !message.trim()"
                                class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 disabled:opacity-50 transition-colors">
                            <svg x-show="loading" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <i data-lucide="send" class="w-3.5 h-3.5" x-show="!loading"></i>
                            <span x-text="isInternal ? 'Add Work Note' : 'Add Comment'">Add Comment</span>
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ── Right: Details Panel ─────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Quick Actions --}}
            @if(auth()->user()->canManageTickets())
            <div class="bg-sn-card rounded-2xl p-5">
                <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Quick Actions</h3>
                <div class="space-y-2">

                    {{-- Status Change --}}
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 bg-white/5 hover:bg-white/10 rounded-xl text-sm font-medium transition-colors">
                            <span class="flex items-center gap-2"><i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-gray-400"></i> Change Status</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-500" :class="open ? 'rotate-180' : ''" style="transition:transform 0.2s"></i>
                        </button>
                        <div x-show="open" x-transition class="mt-2 space-y-1">
                            @foreach($allowedTransitions as $transition)
                            <form method="POST" action="{{ route('tickets.status', $ticket) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $transition->value }}">
                                <button type="submit" class="w-full text-left px-4 py-2 bg-white/3 hover:bg-white/8 rounded-lg text-xs font-medium text-gray-300 hover:text-white transition-colors">
                                    → {{ $transition->value }}
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>

                    {{-- Assign --}}
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 bg-white/5 hover:bg-white/10 rounded-xl text-sm font-medium transition-colors">
                            <span class="flex items-center gap-2"><i data-lucide="user-check" class="w-3.5 h-3.5 text-gray-400"></i> Assign Ticket</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-500" :class="open ? 'rotate-180' : ''" style="transition:transform 0.2s"></i>
                        </button>
                        <div x-show="open" x-transition class="mt-2">
                            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="space-y-2">
                                @csrf @method('PATCH')
                                <select name="assigned_to" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                                    <option value="">Unassign</option>
                                    @foreach($agents as $agent)
                                    <option value="{{ $agent->uid }}" {{ $ticket->assigned_to === $agent->uid ? 'selected' : '' }}>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="assigned_to_name" id="assignedName">
                                <button type="submit" class="w-full bg-primary text-white py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
                                    Update Assignment
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Resolve --}}
                    @if(!$ticket->isResolved())
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2.5 bg-green-500/10 hover:bg-green-500/20 rounded-xl text-sm font-medium text-green-400 transition-colors">
                            <span class="flex items-center gap-2"><i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Resolve Ticket</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5" :class="open ? 'rotate-180' : ''" style="transition:transform 0.2s"></i>
                        </button>
                        <div x-show="open" x-transition class="mt-2">
                            <form method="POST" action="{{ route('tickets.resolve', $ticket) }}" class="space-y-2">
                                @csrf
                                <select name="resolution_code" required class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                                    <option value="">Resolution Code</option>
                                    <option value="Solved">Solved</option>
                                    <option value="Workaround">Workaround Applied</option>
                                    <option value="User Error">User Error</option>
                                    <option value="No Fault Found">No Fault Found</option>
                                    <option value="Duplicate">Duplicate</option>
                                    <option value="Known Error">Known Error</option>
                                </select>
                                <textarea name="resolution_notes" required rows="3" placeholder="Resolution notes..."
                                          class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600 resize-none"></textarea>
                                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-xl text-xs font-bold hover:bg-green-500 transition-colors">
                                    Mark as Resolved
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Ticket Details --}}
            <div class="bg-sn-card rounded-2xl p-5">
                <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Ticket Details</h3>
                <dl class="space-y-3">
                    @foreach([
                        ['Ticket #',       $ticket->ticket_number],
                        ['Caller',         $ticket->caller],
                        ['Affected User',  $ticket->affected_user],
                        ['Channel',        is_object($ticket->channel) ? $ticket->channel->value : $ticket->channel],
                        ['Impact',         is_object($ticket->impact) ? $ticket->impact->value : $ticket->impact],
                        ['Urgency',        is_object($ticket->urgency) ? $ticket->urgency->value : $ticket->urgency],
                        ['Assignment Group', $ticket->assignment_group],
                        ['Assigned To',    $ticket->assigned_to_name],
                        ['Created By',     $ticket->created_by_name],
                        ['Created',        $ticket->created_at?->format('M d, Y H:i')],
                        ['Updated',        $ticket->updated_at?->diffForHumans()],
                    ] as [$label, $value])
                    @if($value)
                    <div class="flex justify-between gap-3">
                        <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest shrink-0">{{ $label }}</dt>
                        <dd class="text-xs text-gray-300 text-right">{{ $value }}</dd>
                    </div>
                    @endif
                    @endforeach
                </dl>
            </div>

            {{-- SLA Details --}}
            @if($ticket->response_deadline || $ticket->resolution_deadline)
            <div class="bg-sn-card rounded-2xl p-5">
                <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">SLA Details</h3>
                <dl class="space-y-3">
                    @if($ticket->response_deadline)
                    <div>
                        <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest mb-1">Response Deadline</dt>
                        <dd class="text-xs text-gray-300">{{ $ticket->response_deadline->format('M d, Y H:i') }}</dd>
                        <dd class="text-[9px] text-{{ $ticket->first_response_at ? 'green' : ($ticket->response_deadline->isPast() ? 'red' : 'gray') }}-400 mt-0.5">
                            {{ $ticket->first_response_at ? 'Met at ' . $ticket->first_response_at->format('H:i') : ($ticket->response_deadline->isPast() ? 'BREACHED' : $ticket->response_deadline->diffForHumans()) }}
                        </dd>
                    </div>
                    @endif
                    @if($ticket->resolution_deadline)
                    <div>
                        <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest mb-1">Resolution Deadline</dt>
                        <dd class="text-xs text-gray-300">{{ $ticket->resolution_deadline->format('M d, Y H:i') }}</dd>
                        <dd class="text-[9px] text-{{ $ticket->resolved_at ? 'green' : ($ticket->resolution_deadline->isPast() ? 'red' : 'gray') }}-400 mt-0.5">
                            {{ $ticket->resolved_at ? 'Resolved at ' . $ticket->resolved_at->format('H:i') : ($ticket->resolution_deadline->isPast() ? 'BREACHED' : $ticket->resolution_deadline->diffForHumans()) }}
                        </dd>
                    </div>
                    @endif
                    @if($ticket->total_paused_time_ms > 0)
                    <div>
                        <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest mb-1">Total Paused Time</dt>
                        <dd class="text-xs text-gray-300">{{ round($ticket->total_paused_time_ms / 60000) }} minutes</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            {{-- Resolution Details --}}
            @if($ticket->isResolved())
            <div class="bg-green-900/20 border border-green-500/20 rounded-2xl p-5">
                <h3 class="text-[10px] font-black text-green-500 uppercase tracking-widest mb-4">Resolution</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest mb-1">Resolution Code</dt>
                        <dd class="text-xs text-gray-300">{{ $ticket->resolution_code }}</dd>
                    </div>
                    @if($ticket->resolution_notes)
                    <div>
                        <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest mb-1">Notes</dt>
                        <dd class="text-xs text-gray-300 leading-relaxed">{{ $ticket->resolution_notes }}</dd>
                    </div>
                    @endif
                    @if($ticket->resolved_by)
                    <div>
                        <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest mb-1">Resolved By</dt>
                        <dd class="text-xs text-gray-300">{{ $ticket->resolved_by }}</dd>
                    </div>
                    @endif
                    @if($ticket->resolved_at)
                    <div>
                        <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest mb-1">Resolved At</dt>
                        <dd class="text-xs text-gray-300">{{ $ticket->resolved_at->format('M d, Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ticketDetail() {
    return {
        async submitComment() {
            // handled by Alpine x-data on the comment form
        }
    };
}

document.addEventListener('alpine:init', () => {
    Alpine.data('slaTimerDetail', (respDeadline, resolDeadline, firstRespAt) => ({
        respText: '...', respBreached: false,
        resolText: '...', resolBreached: false,
        init() { this.update(); setInterval(() => this.update(), 1000); },
        update() {
            const now = Date.now();
            if (firstRespAt) { this.respText = 'MET ✓'; this.respBreached = false; }
            else if (respDeadline) {
                const diff = new Date(respDeadline).getTime() - now;
                this.respBreached = diff <= 0;
                this.respText = diff <= 0 ? 'BREACHED' : this.fmt(diff);
            }
            if (resolDeadline) {
                const diff = new Date(resolDeadline).getTime() - now;
                this.resolBreached = diff <= 0;
                this.resolText = diff <= 0 ? 'BREACHED' : this.fmt(diff);
            }
        },
        fmt(ms) {
            const h = Math.floor(ms / 3600000), m = Math.floor((ms % 3600000) / 60000), s = Math.floor((ms % 60000) / 1000);
            return `${h}h ${m}m ${s}s`;
        }
    }));
});

// Comment submission
document.querySelectorAll('[x-data]').forEach(el => {
    if (el.__x && el.__x.$data.submitComment) {
        el.__x.$data.submitComment = async function() {
            if (!this.message.trim() || this.loading) return;
            this.loading = true;
            try {
                const res = await window.api.post('/api/tickets/{{ $ticket->id }}/comments', {
                    message: this.message,
                    is_internal: this.isInternal
                });
                if (res.id) {
                    this.message = '';
                    window.location.reload();
                }
            } catch(e) {
                alert('Failed to add comment. Please try again.');
            } finally {
                this.loading = false;
            }
        };
    }
});

// Activity filter
function filterActivities(type) {
    document.querySelectorAll('.activity-item').forEach(item => {
        if (type === 'all') { item.style.display = ''; return; }
        if (type === 'system') {
            item.style.display = ['status_change', 'assignment_change', 'system', 'sla_triggered'].includes(item.dataset.type) ? '' : 'none';
        } else {
            item.style.display = item.dataset.type === type ? '' : 'none';
        }
    });
}

// Comment form Alpine binding
document.addEventListener('alpine:init', () => {
    Alpine.data('commentForm', () => ({
        isInternal: false,
        message: '',
        loading: false,
        async submitComment() {
            if (!this.message.trim() || this.loading) return;
            this.loading = true;
            try {
                const res = await window.api.post('/api/tickets/{{ $ticket->id }}/comments', {
                    message: this.message,
                    is_internal: this.isInternal
                });
                if (res.id) { this.message = ''; window.location.reload(); }
            } catch(e) {
                alert('Failed to add comment.');
            } finally { this.loading = false; }
        }
    }));
});
</script>
@endpush
