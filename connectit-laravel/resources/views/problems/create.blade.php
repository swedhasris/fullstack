@extends('layouts.app')
@section('title', 'New Problem')
@section('page-title', 'New Problem Record')
@section('page-subtitle', 'Problem Management')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5"><h2 class="text-base font-bold">Create Problem Record</h2></div>
        <form method="POST" action="{{ route('problems.store') }}" class="p-5 space-y-4">
            @csrf
            @if($errors->any())<div class="bg-red-900/30 border border-red-500/30 text-red-300 rounded-xl px-4 py-3 text-sm"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                       placeholder="Brief description of the problem">
            </div>
            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600 resize-none" placeholder="Detailed description...">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Status</label>
                    <select name="status" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                        @foreach(['Open', 'In Progress', 'Resolved', 'Closed'] as $s)
                        <option value="{{ $s }}" {{ old('status', 'Open') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Priority</label>
                    <select name="priority" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                        @foreach(['1 - Critical', '2 - High', '3 - Moderate', '4 - Low'] as $p)
                        <option value="{{ $p }}" {{ old('priority', '4 - Low') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Category</label>
                    <input type="text" name="category" value="{{ old('category') }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                           placeholder="e.g. Network">
                </div>
            </div>
            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Assign To</label>
                <select name="assigned_to" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                    <option value="">Unassigned</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->uid }}" data-name="{{ $agent->name }}" {{ old('assigned_to') === $agent->uid ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="assigned_to_name" id="assignedName">
            </div>
            <div class="flex justify-between pt-4 border-t border-white/5">
                <a href="{{ route('problems.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition-colors"><i data-lucide="arrow-left" class="w-4 h-4"></i> Cancel</a>
                <button type="submit" class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-primary/90 transition-colors"><i data-lucide="save" class="w-4 h-4"></i> Create Problem</button>
            </div>
        </form>
    </div>
</div>
@endsection
<script>document.querySelector('[name="assigned_to"]')?.addEventListener('change', function() { document.getElementById('assignedName').value = this.selectedOptions[0]?.dataset?.name || ''; });</script>
