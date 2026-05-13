@extends('layouts.app')
@section('title', 'Timesheet')
@section('page-title', 'Timesheet Detail')
@section('page-subtitle', $timesheet->week_start->format('M d') . ' – ' . $timesheet->week_end->format('M d, Y'))

@section('content')
<div class="p-6 max-w-5xl mx-auto space-y-4">
    <div class="flex items-center gap-2 text-[10px] text-gray-500 font-bold uppercase tracking-widest">
        <a href="{{ route('timesheets.index') }}" class="hover:text-primary transition-colors">Timesheets</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-primary">{{ $timesheet->week_start->format('M d, Y') }}</span>
    </div>

    <div class="bg-sn-card rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold">Week of {{ $timesheet->week_start->format('M d, Y') }}</h2>
                <div class="flex items-center gap-3 mt-1">
                    @php $sc = ['Draft' => 'bg-gray-500/20 text-gray-400', 'Submitted' => 'bg-amber-500/20 text-amber-400', 'Approved' => 'bg-green-500/20 text-green-400', 'Rejected' => 'bg-red-500/20 text-red-400']; @endphp
                    <span class="text-[9px] font-black px-2 py-0.5 rounded {{ $sc[$timesheet->status] ?? 'bg-gray-500/20 text-gray-400' }}">{{ $timesheet->status }}</span>
                    <span class="text-xs text-primary font-bold">{{ number_format($timesheet->total_hours, 1) }}h total</span>
                </div>
            </div>
            @if($timesheet->status === 'Draft')
            <form method="POST" action="{{ route('timesheets.submit', $timesheet) }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i> Submit for Approval
                </button>
            </form>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead><tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5">
                    <th class="px-4 py-3">Date</th><th class="px-4 py-3">Task</th><th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3">Start</th><th class="px-4 py-3">End</th><th class="px-4 py-3">Hours</th>
                    <th class="px-4 py-3">Type</th><th class="px-4 py-3">Billable</th>
                </tr></thead>
                <tbody>
                    @forelse($timesheet->timeCards as $card)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="px-4 py-3 text-xs text-gray-300">{{ $card->entry_date->format('M d') }}</td>
                        <td class="px-4 py-3 text-xs font-medium text-white">{{ $card->task ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400 max-w-[200px] truncate">{{ $card->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs font-mono text-gray-400">{{ $card->start_time ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs font-mono text-gray-400">{{ $card->end_time ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs font-bold text-primary">{{ number_format($card->hours_worked, 1) }}h</td>
                        <td class="px-4 py-3 text-[9px] text-gray-400">{{ $card->work_type ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded {{ $card->billable === 'Billable' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                                {{ $card->billable ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-600 text-sm">No time entries yet</td></tr>
                    @endforelse
                </tbody>
                @if($timesheet->timeCards->count())
                <tfoot>
                    <tr class="border-t border-white/10">
                        <td colspan="5" class="px-4 py-3 text-[9px] font-black text-gray-500 uppercase tracking-widest text-right">Total</td>
                        <td class="px-4 py-3 text-sm font-black text-primary">{{ number_format($timesheet->timeCards->sum('hours_worked'), 1) }}h</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
