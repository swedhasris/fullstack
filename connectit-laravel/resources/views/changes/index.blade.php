@extends('layouts.app')
@section('title', 'Change Management')
@section('page-title', 'Change Management')
@section('page-subtitle', 'Change Requests & CAB')

@section('content')
<div class="p-6 space-y-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach([['Draft', $stats['draft'], 'file-text', 'text-gray-400'], ['In Review', $stats['in_review'], 'eye', 'text-amber-400'], ['Approved', $stats['approved'], 'check-circle', 'text-green-400'], ['Implemented', $stats['implemented'], 'check-square', 'text-primary']] as [$l, $v, $i, $c])
        <div class="bg-sn-card p-4 rounded-xl flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center shrink-0"><i data-lucide="{{ $i }}" class="w-4 h-4 {{ $c }}"></i></div>
            <div><div class="text-xl font-black {{ $c }}">{{ $v }}</div><div class="text-[9px] font-black text-gray-500 uppercase tracking-widest">{{ $l }}</div></div>
        </div>
        @endforeach
    </div>

    <div class="bg-sn-card rounded-2xl p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <div class="relative"><i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-500"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Change #, title..."
                       class="w-full bg-white/5 border border-white/10 rounded-xl pl-9 pr-4 py-2 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"></div>
            </div>
            <select name="state" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                <option value="">All States</option>
                @foreach(['Draft', 'In Review', 'Approved', 'Scheduled', 'Implementing', 'Implemented', 'Closed', 'Canceled'] as $s)
                <option value="{{ $s }}" {{ request('state') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <select name="type" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                <option value="">All Types</option>
                @foreach(['Normal', 'Standard', 'Emergency'] as $t)
                <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <button type="submit" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors"><i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter</button>
            <a href="{{ route('changes.create') }}" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors ml-auto"><i data-lucide="plus" class="w-3.5 h-3.5"></i> New Change</a>
        </form>
    </div>

    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead><tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5">
                    <th class="px-5 py-3">Change #</th><th class="px-5 py-3">Title</th><th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">State</th><th class="px-5 py-3">Risk</th><th class="px-5 py-3">Requester</th><th class="px-5 py-3">Planned Start</th>
                </tr></thead>
                <tbody>
                    @forelse($changes as $change)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="px-5 py-3"><a href="{{ route('changes.show', $change) }}" class="text-[10px] font-black text-primary hover:underline">{{ $change->change_number }}</a></td>
                        <td class="px-5 py-3 max-w-[250px]"><a href="{{ route('changes.show', $change) }}" class="text-sm font-medium text-gray-200 hover:text-white truncate block">{{ $change->title }}</a></td>
                        <td class="px-5 py-3"><span class="text-[9px] font-black px-2 py-0.5 rounded {{ $change->type === 'Emergency' ? 'bg-red-500/20 text-red-400' : ($change->type === 'Standard' ? 'bg-green-500/20 text-green-400' : 'bg-blue-500/20 text-blue-400') }}">{{ $change->type }}</span></td>
                        <td class="px-5 py-3"><span class="text-[9px] font-black px-2 py-0.5 rounded bg-white/5 text-gray-300">{{ $change->state }}</span></td>
                        <td class="px-5 py-3"><span class="text-[9px] font-black {{ $change->risk === 'Critical' ? 'text-red-400' : ($change->risk === 'High' ? 'text-orange-400' : ($change->risk === 'Medium' ? 'text-amber-400' : 'text-green-400')) }}">{{ $change->risk }}</span></td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $change->requester_name ?? '—' }}</td>
                        <td class="px-5 py-3 text-[9px] text-gray-500">{{ $change->planned_start_date?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-600 text-sm">No change requests found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($changes->hasPages())<div class="p-4 border-t border-white/5">{{ $changes->links('pagination::tailwind') }}</div>@endif
    </div>
</div>
@endsection
