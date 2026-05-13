@extends('layouts.app')
@section('title', 'Timesheet Approvals')
@section('page-title', 'Timesheet Approvals')
@section('page-subtitle', 'Pending Review')

@section('content')
<div class="p-6 space-y-4">
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5">
            <h3 class="text-sm font-bold">Submitted Timesheets</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $timesheets->total() }} pending approval</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead><tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5">
                    <th class="px-5 py-3">Employee</th><th class="px-5 py-3">Week</th><th class="px-5 py-3">Total Hours</th>
                    <th class="px-5 py-3">Submitted</th><th class="px-5 py-3">Actions</th>
                </tr></thead>
                <tbody>
                    @forelse($timesheets as $ts)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold text-[10px]">{{ strtoupper(substr($ts->user?->name ?? 'U', 0, 1)) }}</div>
                                <div>
                                    <div class="text-sm font-medium text-white">{{ $ts->user?->name ?? 'Unknown' }}</div>
                                    <div class="text-[9px] text-gray-500">{{ $ts->user?->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-300">{{ $ts->week_start->format('M d') }} – {{ $ts->week_end->format('M d, Y') }}</td>
                        <td class="px-5 py-3 text-sm font-bold text-primary">{{ number_format($ts->total_hours, 1) }}h</td>
                        <td class="px-5 py-3 text-[9px] text-gray-500">{{ $ts->submitted_at?->diffForHumans() }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('timesheets.show', $ts) }}" class="text-[9px] font-black px-2 py-1 rounded bg-white/5 text-gray-400 hover:bg-white/10 transition-colors">View</a>
                                <form method="POST" action="{{ route('timesheets.approve', $ts) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[9px] font-black px-2 py-1 rounded bg-green-500/20 text-green-400 hover:bg-green-500/30 transition-colors">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('timesheets.reject', $ts) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-[9px] font-black px-2 py-1 rounded bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-gray-600 text-sm">No timesheets pending approval 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($timesheets->hasPages())<div class="p-4 border-t border-white/5">{{ $timesheets->links('pagination::tailwind') }}</div>@endif
    </div>
</div>
@endsection
