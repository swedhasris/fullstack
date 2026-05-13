<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use App\Models\TimeCard;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TimesheetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * My timesheets.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $timesheets = Timesheet::where('user_id', $user->uid)
            ->with('timeCards')
            ->orderByDesc('week_start')
            ->paginate(10);

        return view('timesheets.index', compact('timesheets'));
    }

    /**
     * Get or create timesheet for current week.
     */
    public function getOrCreate(Request $request)
    {
        $user = Auth::user();
        $weekStart = Carbon::now()->startOfWeek()->toDateString();
        $weekEnd   = Carbon::now()->endOfWeek()->toDateString();

        $timesheet = Timesheet::firstOrCreate(
            ['user_id' => $user->uid, 'week_start' => $weekStart],
            ['week_end' => $weekEnd, 'status' => 'Draft', 'total_hours' => 0]
        );

        return response()->json($timesheet->load('timeCards'));
    }

    /**
     * Show timesheet detail.
     */
    public function show(Timesheet $timesheet)
    {
        $this->authorizeTimesheet($timesheet);
        $timesheet->load('timeCards');
        return view('timesheets.show', compact('timesheet'));
    }

    /**
     * Submit timesheet for approval.
     */
    public function submit(Timesheet $timesheet)
    {
        $this->authorizeTimesheet($timesheet);

        $timesheet->update([
            'status'       => 'Submitted',
            'submitted_at' => now(),
            'total_hours'  => $timesheet->timeCards->sum('hours_worked'),
        ]);

        return back()->with('success', 'Timesheet submitted for approval.');
    }

    /**
     * Approve timesheet (admin/manager).
     */
    public function approve(Timesheet $timesheet)
    {
        if (!Auth::user()->canApproveTimesheets()) {
            abort(403);
        }

        $timesheet->update(['status' => 'Approved']);
        return back()->with('success', 'Timesheet approved.');
    }

    /**
     * Reject timesheet.
     */
    public function reject(Request $request, Timesheet $timesheet)
    {
        if (!Auth::user()->canApproveTimesheets()) {
            abort(403);
        }

        $timesheet->update(['status' => 'Rejected']);
        return back()->with('success', 'Timesheet rejected.');
    }

    /**
     * Timesheet approvals list (for managers).
     */
    public function approvals()
    {
        if (!Auth::user()->canApproveTimesheets()) {
            abort(403);
        }

        $timesheets = Timesheet::where('status', 'Submitted')
            ->with(['user', 'timeCards'])
            ->orderByDesc('submitted_at')
            ->paginate(20);

        return view('timesheets.approvals', compact('timesheets'));
    }

    /**
     * Store time card.
     */
    public function storeTimeCard(Request $request)
    {
        $validated = $request->validate([
            'timesheet_id'      => 'required|exists:timesheets,id',
            'entry_date'        => 'required|date',
            'task'              => 'nullable|string|max:255',
            'hours_worked'      => 'required|numeric|min:0|max:24',
            'description'       => 'nullable|string',
            'short_description' => 'nullable|string|max:255',
            'start_time'        => 'nullable|string',
            'end_time'          => 'nullable|string',
            'deduct'            => 'nullable|numeric|min:0',
            'work_type'         => 'nullable|string|max:50',
            'billable'          => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $timeCard = TimeCard::create(array_merge($validated, [
            'user_id' => $user->uid,
            'status'  => 'Draft',
        ]));

        // Update timesheet total
        $timesheet = Timesheet::find($validated['timesheet_id']);
        $timesheet->update(['total_hours' => $timesheet->timeCards()->sum('hours_worked')]);

        return response()->json($timeCard, 201);
    }

    protected function authorizeTimesheet(Timesheet $timesheet): void
    {
        $user = Auth::user();
        if ($timesheet->user_id !== $user->uid && !$user->canApproveTimesheets()) {
            abort(403);
        }
    }
}
