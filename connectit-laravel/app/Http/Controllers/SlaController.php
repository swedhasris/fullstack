<?php

namespace App\Http\Controllers;

use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Enums\TicketPriority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SlaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * SLA Management page.
     */
    public function index()
    {
        $policies = SlaPolicy::orderBy('priority')->get();

        $now = now();

        // SLA metrics
        $metrics = [
            'total_open'       => Ticket::open()->count(),
            'sla_breached'     => Ticket::open()->whereNotNull('resolution_deadline')->where('resolution_deadline', '<', $now)->count(),
            'sla_at_risk'      => Ticket::open()->whereNotNull('resolution_deadline')
                                    ->where('resolution_deadline', '>', $now)
                                    ->where('resolution_deadline', '<', $now->copy()->addHours(2))->count(),
            'sla_healthy'      => Ticket::open()->whereNotNull('resolution_deadline')->where('resolution_deadline', '>', $now->copy()->addHours(2))->count(),
            'compliance_rate'  => $this->calculateComplianceRate(),
        ];

        // Breached tickets
        $breachedTickets = Ticket::open()
            ->whereNotNull('resolution_deadline')
            ->where('resolution_deadline', '<', $now)
            ->with('assignee')
            ->orderBy('resolution_deadline')
            ->limit(20)
            ->get();

        // At-risk tickets
        $atRiskTickets = Ticket::open()
            ->whereNotNull('resolution_deadline')
            ->where('resolution_deadline', '>', $now)
            ->where('resolution_deadline', '<', $now->copy()->addHours(4))
            ->with('assignee')
            ->orderBy('resolution_deadline')
            ->limit(20)
            ->get();

        return view('sla.index', compact('policies', 'metrics', 'breachedTickets', 'atRiskTickets'));
    }

    /**
     * Store new SLA policy.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'priority'              => 'required|string',
            'category'              => 'nullable|string|max:100',
            'response_time_hours'   => 'required|integer|min:1',
            'resolution_time_hours' => 'required|integer|min:1',
            'description'           => 'nullable|string',
            'is_active'             => 'boolean',
        ]);

        $policy = SlaPolicy::create($validated);

        if ($request->expectsJson()) {
            return response()->json($policy, 201);
        }

        return back()->with('success', 'SLA Policy created successfully.');
    }

    /**
     * Update SLA policy.
     */
    public function update(Request $request, SlaPolicy $slaPolicy)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'response_time_hours'   => 'required|integer|min:1',
            'resolution_time_hours' => 'required|integer|min:1',
            'description'           => 'nullable|string',
            'is_active'             => 'boolean',
        ]);

        $slaPolicy->update($validated);

        if ($request->expectsJson()) {
            return response()->json($slaPolicy);
        }

        return back()->with('success', 'SLA Policy updated successfully.');
    }

    /**
     * Delete SLA policy.
     */
    public function destroy(SlaPolicy $slaPolicy)
    {
        $slaPolicy->delete();
        return back()->with('success', 'SLA Policy deleted.');
    }

    /**
     * Calculate overall SLA compliance rate.
     */
    protected function calculateComplianceRate(): float
    {
        $total    = Ticket::whereIn('status', ['Resolved', 'Closed'])->count();
        $onTime   = Ticket::whereIn('status', ['Resolved', 'Closed'])
                          ->where(function ($q) {
                              $q->whereNull('resolution_deadline')
                                ->orWhereColumn('resolved_at', '<=', 'resolution_deadline');
                          })->count();

        return $total > 0 ? round(($onTime / $total) * 100, 1) : 100.0;
    }
}
