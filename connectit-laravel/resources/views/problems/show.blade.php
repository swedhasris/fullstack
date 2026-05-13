@extends('layouts.app')
@section('title', $problem->problem_number)
@section('page-title', 'Problem Detail')
@section('page-subtitle', $problem->problem_number)

@section('content')
<div class="p-6 max-w-4xl mx-auto space-y-4">
    <div class="flex items-center gap-2 text-[10px] text-gray-500 font-bold uppercase tracking-widest">
        <a href="{{ route('problems.index') }}" class="hover:text-primary transition-colors">Problems</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-primary">{{ $problem->problem_number }}</span>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 space-y-4">
            <div class="bg-sn-card rounded-2xl p-5">
                <h2 class="text-lg font-bold mb-3">{{ $problem->title }}</h2>
                @if($problem->description)<p class="text-sm text-gray-300 leading-relaxed">{{ $problem->description }}</p>@endif
            </div>

            @foreach([['Root Cause', $problem->root_cause, 'bug'], ['Workaround', $problem->workaround, 'wrench'], ['Resolution', $problem->resolution, 'check-circle']] as [$label, $content, $icon])
            @if($content)
            <div class="bg-sn-card rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="{{ $icon }}" class="w-4 h-4 text-primary"></i>
                    <h3 class="text-sm font-bold">{{ $label }}</h3>
                </div>
                <p class="text-sm text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $content }}</p>
            </div>
            @endif
            @endforeach
        </div>

        <div class="space-y-4">
            <div class="bg-sn-card rounded-2xl p-5">
                <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Details</h3>
                <form method="POST" action="{{ route('problems.update', $problem) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[9px] font-black text-gray-600 uppercase tracking-widest mb-1">Status</label>
                        <select name="status" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                            @foreach(['Open', 'In Progress', 'Resolved', 'Closed'] as $s)
                            <option value="{{ $s }}" {{ $problem->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-gray-600 uppercase tracking-widest mb-1">Priority</label>
                        <select name="priority" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                            @foreach(['1 - Critical', '2 - High', '3 - Moderate', '4 - Low'] as $p)
                            <option value="{{ $p }}" {{ $problem->priority === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="title" value="{{ $problem->title }}">
                    <input type="hidden" name="description" value="{{ $problem->description }}">
                    <input type="hidden" name="root_cause" value="{{ $problem->root_cause }}">
                    <input type="hidden" name="workaround" value="{{ $problem->workaround }}">
                    <input type="hidden" name="resolution" value="{{ $problem->resolution }}">
                    <input type="hidden" name="assigned_to" value="{{ $problem->assigned_to }}">
                    <input type="hidden" name="assigned_to_name" value="{{ $problem->assigned_to_name }}">
                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">Update</button>
                </form>
                <dl class="space-y-2 mt-4 pt-4 border-t border-white/5">
                    @foreach([['Reported By', $problem->reported_by_name], ['Category', $problem->category], ['Related Incidents', $problem->related_incidents], ['Created', $problem->created_at->format('M d, Y')]] as [$l, $v])
                    @if($v)
                    <div class="flex justify-between gap-3">
                        <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest">{{ $l }}</dt>
                        <dd class="text-xs text-gray-300">{{ $v }}</dd>
                    </div>
                    @endif
                    @endforeach
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
