@extends('layouts.app')
@section('title', 'Problem Management')
@section('page-title', 'Problem Management')
@section('page-subtitle', 'Root Cause Analysis')

@section('content')
<div class="p-6 space-y-4">
    <div class="grid grid-cols-3 gap-3">
        @foreach([['Open', $stats['open'], 'alert-circle', 'text-red-400'], ['In Progress', $stats['in_progress'], 'loader', 'text-amber-400'], ['Resolved', $stats['resolved'], 'check-circle', 'text-green-400']] as [$l, $v, $i, $c])
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
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Problem #, title..."
                       class="w-full bg-white/5 border border-white/10 rounded-xl pl-9 pr-4 py-2 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"></div>
            </div>
            <select name="status" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                <option value="">All Statuses</option>
                @foreach(['Open', 'In Progress', 'Resolved', 'Closed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <button type="submit" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors"><i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter</button>
            <a href="{{ route('problems.create') }}" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors ml-auto"><i data-lucide="plus" class="w-3.5 h-3.5"></i> New Problem</a>
        </form>
    </div>

    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead><tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5">
                    <th class="px-5 py-3">Problem #</th><th class="px-5 py-3">Title</th><th class="px-5 py-3">Priority</th>
                    <th class="px-5 py-3">Status</th><th class="px-5 py-3">Assigned To</th><th class="px-5 py-3">Related Incidents</th><th class="px-5 py-3">Created</th>
                </tr></thead>
                <tbody>
                    @forelse($problems as $problem)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="px-5 py-3"><a href="{{ route('problems.show', $problem) }}" class="text-[10px] font-black text-primary hover:underline">{{ $problem->problem_number }}</a></td>
                        <td class="px-5 py-3 max-w-[250px]"><a href="{{ route('problems.show', $problem) }}" class="text-sm font-medium text-gray-200 hover:text-white truncate block">{{ $problem->title }}</a></td>
                        <td class="px-5 py-3"><span class="text-[9px] font-black px-2 py-0.5 rounded bg-white/5 text-gray-300">{{ $problem->priority }}</span></td>
                        <td class="px-5 py-3"><span class="text-[9px] font-black px-2 py-0.5 rounded {{ $problem->status === 'Open' ? 'bg-red-500/20 text-red-400' : ($problem->status === 'Resolved' ? 'bg-green-500/20 text-green-400' : 'bg-amber-500/20 text-amber-400') }}">{{ $problem->status }}</span></td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $problem->assigned_to_name ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs text-blue-400 font-bold">{{ $problem->related_incidents }}</td>
                        <td class="px-5 py-3 text-[9px] text-gray-500">{{ $problem->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-600 text-sm">No problems found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($problems->hasPages())<div class="p-4 border-t border-white/5">{{ $problems->links('pagination::tailwind') }}</div>@endif
    </div>
</div>
@endsection
