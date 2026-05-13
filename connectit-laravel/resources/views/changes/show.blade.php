@extends('layouts.app')
@section('title', $change->change_number)
@section('page-title', 'Change Detail')
@section('page-subtitle', $change->change_number)

@section('content')
<div class="p-6 max-w-4xl mx-auto space-y-4">
    <div class="flex items-center gap-2 text-[10px] text-gray-500 font-bold uppercase tracking-widest">
        <a href="{{ route('changes.index') }}" class="hover:text-primary transition-colors">Changes</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-primary">{{ $change->change_number }}</span>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 space-y-4">
            <div class="bg-sn-card rounded-2xl p-5">
                <div class="flex items-start justify-between mb-3">
                    <h2 class="text-lg font-bold">{{ $change->title }}</h2>
                    <div class="flex gap-2">
                        <span class="text-[9px] font-black px-2 py-0.5 rounded {{ $change->type === 'Emergency' ? 'bg-red-500/20 text-red-400' : 'bg-blue-500/20 text-blue-400' }}">{{ $change->type }}</span>
                        <span class="text-[9px] font-black px-2 py-0.5 rounded bg-white/5 text-gray-300">{{ $change->state }}</span>
                    </div>
                </div>
                @if($change->description)<p class="text-sm text-gray-300 leading-relaxed">{{ $change->description }}</p>@endif
            </div>

            @foreach([['Impact', $change->impact, 'zap'], ['Rollback Plan', $change->rollback_plan, 'rotate-ccw'], ['Affected Services', $change->affected_services, 'layers']] as [$label, $content, $icon])
            @if($content)
            <div class="bg-sn-card rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-3"><i data-lucide="{{ $icon }}" class="w-4 h-4 text-primary"></i><h3 class="text-sm font-bold">{{ $label }}</h3></div>
                <p class="text-sm text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $content }}</p>
            </div>
            @endif
            @endforeach
        </div>

        <div class="space-y-4">
            <div class="bg-sn-card rounded-2xl p-5">
                <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Change Details</h3>
                <form method="POST" action="{{ route('changes.update', $change) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[9px] font-black text-gray-600 uppercase tracking-widest mb-1">State</label>
                        <select name="state" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                            @foreach(['Draft', 'In Review', 'Approved', 'Scheduled', 'Implementing', 'Implemented', 'Closed', 'Canceled'] as $s)
                            <option value="{{ $s }}" {{ $change->state === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-gray-600 uppercase tracking-widest mb-1">Approval Status</label>
                        <select name="approval_status" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                            @foreach(['Not Required', 'Pending', 'Approved', 'Rejected'] as $s)
                            <option value="{{ $s }}" {{ $change->approval_status === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="title" value="{{ $change->title }}">
                    <input type="hidden" name="type" value="{{ $change->type }}">
                    <input type="hidden" name="risk" value="{{ $change->risk }}">
                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">Update</button>
                </form>
                <dl class="space-y-2 mt-4 pt-4 border-t border-white/5">
                    @foreach([['Requester', $change->requester_name], ['Risk', $change->risk], ['Category', $change->category], ['Planned Start', $change->planned_start_date?->format('M d, Y H:i')], ['Planned End', $change->planned_end_date?->format('M d, Y H:i')]] as [$l, $v])
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
