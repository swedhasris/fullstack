@extends('layouts.app')
@section('title', 'Timesheets')
@section('page-title', 'My Timesheets')
@section('page-subtitle', 'Time Tracking')

@section('content')
<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ $timesheets->total() }} timesheets</p>
        <a href="{{ route('timesheets.approvals') }}" class="text-xs text-primary hover:underline font-bold" x-show="{{ auth()->user()->canApproveTimesheets() ? 'true' : 'false' }}">
            View Pending Approvals
        </a>
    </div>

    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead><tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5">
                    <th class="px-5 py-3">Week</th><th class="px-5 py-3">Total Hours</th><th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Submitted</th><th class="px-5 py-3">Actions</th>
                </tr></thead>
                <tbody>
                    @forelse($timesheets as $ts)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('timesheets.show', $ts) }}" class="text-sm font-medium text-white hover:text-primary transition-colors">
                                {{ $ts->week_start->format('M d') }} – {{ $ts->week_end->format('M d, Y') }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-sm font-bold text-primary">{{ number_format($ts->total_hours, 1) }}h</td>
                        <td class="px-5 py-3">
                            @php $sc = ['Draft' => 'bg-gray-500/20 text-gray-400', 'Submitted' => 'bg-amber-500/20 text-amber-400', 'Approved' => 'bg-green-500/20 text-green-400', 'Rejected' => 'bg-red-500/20 text-red-400']; @endphp
                            <span class="text-[9px] font-black px-2 py-0.5 rounded {{ $sc[$ts->status] ?? 'bg-gray-500/20 text-gray-400' }}">{{ $ts->status }}</span>
                        </td>
                        <td class="px-5 py-3 text-[9px] text-gray-500">{{ $ts->submitted_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('timesheets.show', $ts) }}" class="p-1.5 hover:bg-white/10 rounded-lg transition-colors"><i data-lucide="eye" class="w-3.5 h-3.5 text-gray-400"></i></a>
                                @if($ts->status === 'Draft')
                                <form method="POST" action="{{ route('timesheets.submit', $ts) }}">
                                    @csrf
                                    <button type="submit" class="text-[9px] font-black px-2 py-1 rounded bg-primary/10 text-primary hover:bg-primary/20 transition-colors">Submit</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-gray-600 text-sm">No timesheets found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($timesheets->hasPages())<div class="p-4 border-t border-white/5">{{ $timesheets->links('pagination::tailwind') }}</div>@endif
    </div>
</div>
@endsection
