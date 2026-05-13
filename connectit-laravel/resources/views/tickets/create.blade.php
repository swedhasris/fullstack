@extends('layouts.app')

@section('title', 'New Ticket')
@section('page-title', 'Create Incident')
@section('page-subtitle', 'New Ticket · Service Desk')

@section('content')
<div class="p-6 max-w-4xl mx-auto" x-data="createTicket()">

    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5">
            <h2 class="text-base font-bold">New Incident Ticket</h2>
            <p class="text-xs text-gray-500 mt-0.5">Fill in the details below to create a new incident ticket</p>
        </div>

        <form method="POST" action="{{ route('tickets.store') }}" class="p-5 space-y-5" @submit="loading = true">
            @csrf

            {{-- Row 1: Caller + Affected User --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Caller / Reporter *</label>
                    <input type="text" name="caller" value="{{ old('caller', auth()->user()->name) }}" required
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                           placeholder="Name or email of the caller">
                    <input type="hidden" name="caller_user_id" value="{{ auth()->user()->uid }}">
                    <input type="hidden" name="caller_email" value="{{ auth()->user()->email }}">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Affected User</label>
                    <input type="text" name="affected_user" value="{{ old('affected_user') }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                           placeholder="Who is affected by this issue?">
                </div>
            </div>

            {{-- Title --}}
            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Short Description / Title *</label>
                <div class="relative">
                    <input type="text" name="title" value="{{ old('title') }}" required x-model="title"
                           @input.debounce.500ms="getSuggestion()"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                           placeholder="Brief description of the issue...">
                    <div x-show="loadingSuggestion" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </div>
                </div>
                {{-- AI Suggestion --}}
                <div x-show="suggestion" x-transition class="mt-2 bg-primary/5 border border-primary/20 rounded-xl p-3">
                    <div class="flex items-start gap-2">
                        <i data-lucide="bot" class="w-4 h-4 text-primary shrink-0 mt-0.5"></i>
                        <div>
                            <div class="text-[9px] font-black text-primary uppercase tracking-widest mb-1">Kiru AI Suggestion</div>
                            <p class="text-xs text-gray-300 leading-relaxed" x-text="suggestion"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Description</label>
                <textarea name="description" rows="4" value="{{ old('description') }}"
                          class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600 resize-none"
                          placeholder="Detailed description of the issue, steps to reproduce, error messages...">{{ old('description') }}</textarea>
            </div>

            {{-- Row 2: Category + Subcategory + Service --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Category</label>
                    <select name="category" x-model="category" @change="subcategory = ''; service = ''"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                        <option value="">Select Category</option>
                        @foreach(['Hardware', 'Software', 'Network', 'Security', 'Access', 'Email', 'Printer', 'Phone', 'Database', 'Application', 'Other'] as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Subcategory</label>
                    <input type="text" name="subcategory" value="{{ old('subcategory') }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                           placeholder="e.g. Laptop, VPN, Password">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Service</label>
                    <input type="text" name="service" value="{{ old('service') }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                           placeholder="e.g. Office 365, SAP">
                </div>
            </div>

            {{-- Row 3: Impact + Urgency + Priority Preview --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Impact *</label>
                    <select name="impact" required x-model="impact" @change="calcPriority()"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                        <option value="">Select Impact</option>
                        @foreach($impacts as $impact)
                        <option value="{{ $impact->value }}" {{ old('impact') === $impact->value ? 'selected' : '' }}>{{ $impact->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Urgency *</label>
                    <select name="urgency" required x-model="urgency" @change="calcPriority()"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                        <option value="">Select Urgency</option>
                        @foreach($urgencies as $urgency)
                        <option value="{{ $urgency->value }}" {{ old('urgency') === $urgency->value ? 'selected' : '' }}>{{ $urgency->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Calculated Priority</label>
                    <div class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm font-bold"
                         :class="priorityColor" x-text="priority || 'Select Impact & Urgency'"></div>
                </div>
            </div>

            {{-- Row 4: Channel + Assignment --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Channel</label>
                    <select name="channel" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                        @foreach($channels as $channel)
                        <option value="{{ $channel->value }}" {{ old('channel', 'Self-service') === $channel->value ? 'selected' : '' }}>{{ $channel->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Assignment Group</label>
                    <input type="text" name="assignment_group" value="{{ old('assignment_group', 'Service Desk') }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Assign To</label>
                    <select name="assigned_to" @change="updateAssignedName($event)"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                        <option value="">Unassigned</option>
                        @foreach($agents as $agent)
                        <option value="{{ $agent->uid }}" data-name="{{ $agent->name }}" {{ old('assigned_to') === $agent->uid ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="assigned_to_name" id="assignedToName">
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between pt-4 border-t border-white/5">
                <a href="{{ route('tickets.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Cancel
                </a>
                <button type="submit" :disabled="loading"
                        class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-primary/90 disabled:opacity-70 transition-colors">
                    <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <i data-lucide="ticket" class="w-4 h-4" x-show="!loading"></i>
                    <span x-text="loading ? 'Creating...' : 'Create Ticket'">Create Ticket</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function createTicket() {
    return {
        title: '{{ old('title') }}',
        impact: '{{ old('impact') }}',
        urgency: '{{ old('urgency') }}',
        category: '{{ old('category') }}',
        priority: '',
        priorityColor: 'text-gray-400',
        suggestion: '',
        loadingSuggestion: false,
        loading: false,

        calcPriority() {
            if (!this.impact || !this.urgency) { this.priority = ''; return; }
            const i = parseInt(this.impact[0]);
            const u = parseInt(this.urgency[0]);
            const sum = i + u;
            const map = {
                2: ['1 - Critical', 'text-red-400'],
                3: ['2 - High', 'text-orange-400'],
                4: ['3 - Moderate', 'text-amber-400'],
            };
            const [p, c] = map[sum] || ['4 - Low', 'text-blue-400'];
            this.priority = p;
            this.priorityColor = c;
        },

        async getSuggestion() {
            if (this.title.length < 10) { this.suggestion = ''; return; }
            this.loadingSuggestion = true;
            try {
                const data = await window.api.post('/api/ai/suggest', { text: this.title });
                this.suggestion = data.suggestion || '';
            } catch(e) {
                this.suggestion = '';
            } finally {
                this.loadingSuggestion = false;
            }
        },

        updateAssignedName(event) {
            const opt = event.target.selectedOptions[0];
            document.getElementById('assignedToName').value = opt?.dataset?.name || '';
        }
    };
}
</script>
@endpush
