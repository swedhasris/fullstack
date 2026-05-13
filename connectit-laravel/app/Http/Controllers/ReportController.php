<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketActivity;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Reports dashboard.
     */
    public function index(Request $request)
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfDay();

        // Volume metrics
        $volumeMetrics = [
            'total_created'  => Ticket::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_resolved' => Ticket::whereIn('status', ['Resolved', 'Closed'])
                                      ->whereBetween('resolved_at', [$dateFrom, $dateTo])->count(),
            'avg_resolution' => $this->avgResolutionHours($dateFrom, $dateTo),
            'sla_compliance' => $this->slaComplianceRate($dateFrom, $dateTo),
        ];

        // By priority
        $byPriority = Ticket::select('priority', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('priority')
            ->get();

        // By status
        $byStatus = Ticket::select('status', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('status')
            ->get();

        // By category
        $byCategory = Ticket::select('category', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Daily trend
        $dailyTrend = Ticket::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as created'),
                DB::raw('SUM(CASE WHEN status IN ("Resolved","Closed") THEN 1 ELSE 0 END) as resolved')
            )
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Agent performance
        $agentPerformance = User::whereIn('role', [UserRole::Agent->value, UserRole::Admin->value, UserRole::SubAdmin->value])
            ->where('is_active', true)
            ->get()
            ->map(function ($user) use ($dateFrom, $dateTo) {
                $resolved = Ticket::where('assigned_to', $user->uid)
                    ->whereIn('status', ['Resolved', 'Closed'])
                    ->whereBetween('resolved_at', [$dateFrom, $dateTo])
                    ->count();

                $avgHours = Ticket::where('assigned_to', $user->uid)
                    ->whereIn('status', ['Resolved', 'Closed'])
                    ->whereBetween('resolved_at', [$dateFrom, $dateTo])
                    ->whereNotNull('resolution_duration')
                    ->avg('resolution_duration');

                return [
                    'name'         => $user->name,
                    'resolved'     => $resolved,
                    'avg_hours'    => $avgHours ? round($avgHours / 3600, 1) : null,
                    'open'         => Ticket::where('assigned_to', $user->uid)->open()->count(),
                ];
            })
            ->sortByDesc('resolved')
            ->values();

        // SLA breach by priority
        $slaBreachByPriority = Ticket::select('priority', DB::raw('count(*) as breached'))
            ->whereIn('status', ['Resolved', 'Closed'])
            ->whereBetween('resolved_at', [$dateFrom, $dateTo])
            ->whereNotNull('resolution_deadline')
            ->whereColumn('resolved_at', '>', 'resolution_deadline')
            ->groupBy('priority')
            ->get();

        return view('reports.index', compact(
            'volumeMetrics', 'byPriority', 'byStatus', 'byCategory',
            'dailyTrend', 'agentPerformance', 'slaBreachByPriority',
            'dateFrom', 'dateTo'
        ));
    }

    protected function avgResolutionHours(Carbon $from, Carbon $to): ?float
    {
        $avg = Ticket::whereIn('status', ['Resolved', 'Closed'])
            ->whereBetween('resolved_at', [$from, $to])
            ->whereNotNull('resolution_duration')
            ->avg('resolution_duration');

        return $avg ? round($avg / 3600, 1) : null;
    }

    protected function slaComplianceRate(Carbon $from, Carbon $to): float
    {
        $total  = Ticket::whereIn('status', ['Resolved', 'Closed'])->whereBetween('resolved_at', [$from, $to])->count();
        $onTime = Ticket::whereIn('status', ['Resolved', 'Closed'])
            ->whereBetween('resolved_at', [$from, $to])
            ->where(function ($q) {
                $q->whereNull('resolution_deadline')
                  ->orWhereColumn('resolved_at', '<=', 'resolution_deadline');
            })->count();

        return $total > 0 ? round(($onTime / $total) * 100, 1) : 100.0;
    }
}
