@extends('layouts.app')
@section('title', 'New Change')
@section('page-title', 'New Change Request')
@section('page-subtitle', 'Change Management')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5"><h2 class="text-base font-bold">Create Change Request</h2></div>
        <form method="POST" action="{{ route('changes.store') }}" class="p-5 space-y-4">
            @csrf
            @if($errors->any())<div class="bg-red-900/30 border border-red-500/30 text-red-300 rounded-xl px-4 py-3 text-sm"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                       placeholder="Brief description of the change">
            </div>
            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600 resize-none" placeholder="Detailed description of the change...">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Type *</label>
                    <select name="type" required class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                        @foreach(['Normal', 'Standard', 'Emergency'] as $t)
                        <option value="{{ $t }}" {{ old('type', 'Normal') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">State *</label>
                    <select name="state" required class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                        @foreach(['Draft', 'In Review', 'Approved'] as $s)
                        <option value="{{ $s }}" {{ old('state', 'Draft') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Risk *</label>
                    <select name="risk" required class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                        @foreach(['Low', 'Medium', 'High', 'Critical'] as $r)
                        <option value="{{ $r }}" {{ old('risk', 'Low') === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Rollback Plan</label>
                <textarea name="rollback_plan" rows="2" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600 resize-none" placeholder="Steps to rollback if change fails...">{{ old('rollback_plan') }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Planned Start</label>
                    <input type="datetime-local" name="planned_start_date" value="{{ old('planned_start_date') }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Planned End</label>
                    <input type="datetime-local" name="planned_end_date" value="{{ old('planned_end_date') }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                </div>
            </div>
            <div class="flex justify-between pt-4 border-t border-white/5">
                <a href="{{ route('changes.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition-colors"><i data-lucide="arrow-left" class="w-4 h-4"></i> Cancel</a>
                <button type="submit" class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-primary/90 transition-colors"><i data-lucide="save" class="w-4 h-4"></i> Create Change</button>
            </div>
        </form>
    </div>
</div>
@endsection
