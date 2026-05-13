<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketActivity;
use App\Enums\UserRole;
use App\Enums\TicketPriority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $now = now();
        $weekStart = $now->copy()->startOfWeek();
        $prevWeekStart = $now->copy()->subWeek()->startOfWeek();
        $prevWeekEnd = $now->copy()->subWeek()->endOfWeek();
        $monthStart = $now->copy()->startOfMonth();
        
        $criticalPriority = TicketPriority::Critical->value;

        // KPI Matrix (Inspired by BrightGauge Top Row)
        $stats = [
            'total_incidents' => Ticket::count(),
            'waiting_for_tl' => Ticket::unassigned()->open()->count(),
            'aging_5_days' => Ticket::open()->where('created_at', '<', $now->copy()->subDays(5))->count(),
            'currently_open' => Ticket::open()->count(),
            'not_updated_today' => Ticket::open()->where('updated_at', '<', $today)->count(),
            'escalated_tickets' => Ticket::where('priority', $criticalPriority)->count(),
            'resolved_today' => Ticket::whereIn('status', ['Resolved', 'Closed'])->whereDate('resolved_at', $today)->count(),
            'active_technicians' => User::whereIn('role', [UserRole::Agent->value, UserRole::Admin->value])->where('is_active', true)->count(),
            'pending_tickets' => Ticket::whereIn('status', ['pending', 'waiting_for_info', 'On Hold'])->count(),
            'overdue_tickets' => Ticket::open()->whereNotNull('resolution_deadline')->where('resolution_deadline', '<', $now)->count(),
            'waiting_for_response' => Ticket::where('status', 'waiting_for_info')->count(),
            'stale_tickets' => Ticket::open()->where('updated_at', '<', $now->copy()->subHours(24))->count(),
            'sla_breached' => Ticket::whereNotNull('resolution_deadline')->where('resolution_deadline', '<', $now)->count(),
        ];

        // Aging Data
        $agingData = [
            'new' => Ticket::open()->where('created_at', '>', $now->copy()->subHours(24))->count(),
            '1_3_days' => Ticket::open()->whereBetween('created_at', [$now->copy()->subDays(3), $now->copy()->subHours(24)])->count(),
            '3_7_days' => Ticket::open()->whereBetween('created_at', [$now->copy()->subDays(7), $now->copy()->subDays(3)])->count(),
            'older' => Ticket::open()->where('created_at', '<', $now->copy()->subDays(7))->count(),
        ];

        // Distribution Data
        $priorityData = Ticket::select('priority', DB::raw('count(*) as count'))->groupBy('priority')->get();
        $statusData = Ticket::select('status', DB::raw('count(*) as count'))->groupBy('status')->get();

        // Heatmap Data: Tickets Closed By Tech (Last 7 Days)
        $heatmapData = DB::table('tickets')
            ->select('assigned_to_name', DB::raw('DATE(resolved_at) as date'), DB::raw('count(*) as count'))
            ->whereIn('status', ['Resolved', 'Closed'])
            ->where('resolved_at', '>=', $now->copy()->subDays(7))
            ->whereNotNull('assigned_to_name')
            ->groupBy('assigned_to_name', 'date')
            ->get();

        // Leaderboards
        $techWorkload = User::whereIn('role', [UserRole::Agent->value, UserRole::Admin->value])
            ->withCount(['assignedTickets' => function($q) {
                $q->whereIn('status', ['New', 'In Progress', 'Assigned']);
            }])
            ->orderBy('assigned_tickets_count', 'desc')
            ->get();

        $performanceLeaderboard = User::whereIn('role', [UserRole::Agent->value, UserRole::Admin->value])
            ->select('uid', 'name')
            ->get()
            ->map(function($user) use ($weekStart, $prevWeekStart, $prevWeekEnd, $monthStart) {
                return (object)[
                    'name' => $user->name,
                    'current_week' => Ticket::where('assigned_to', $user->uid)->whereIn('status', ['Resolved', 'Closed'])->where('resolved_at', '>=', $weekStart)->count(),
                    'prev_week' => Ticket::where('assigned_to', $user->uid)->whereIn('status', ['Resolved', 'Closed'])->whereBetween('resolved_at', [$prevWeekStart, $prevWeekEnd])->count(),
                    'mtd' => Ticket::where('assigned_to', $user->uid)->whereIn('status', ['Resolved', 'Closed'])->where('resolved_at', '>=', $monthStart)->count(),
                ];
            })
            ->sortByDesc('mtd')
            ->values();

        // Operational Monitoring
        $attentionTickets = Ticket::open()
            ->with(['activities' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(1);
            }])
            ->get()
            ->filter(function($ticket) {
                $lastActivity = $ticket->activities->first();
                return $lastActivity && !in_array($lastActivity->created_by, User::whereIn('role', [UserRole::Agent->value, UserRole::Admin->value])->pluck('uid')->toArray());
            })
            ->take(10);

        $weeklyTrends = Ticket::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', $now->copy()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $recentTickets = Ticket::orderBy('created_at', 'desc')->limit(10)->get();
        $recentActivities = TicketActivity::with('ticket')->orderBy('created_at', 'desc')->limit(10)->get();
        $unassignedTickets = Ticket::unassigned()->open()->limit(5)->get();

        return view('dashboard', compact(
            'stats', 'agingData', 'priorityData', 'statusData',
            'heatmapData', 'techWorkload', 'performanceLeaderboard',
            'attentionTickets', 'weeklyTrends', 'recentTickets',
            'recentActivities', 'unassignedTickets'
        ));
    }

    public function stats()
    {
        return response()->json([
            'stats' => [
                'total_tickets' => Ticket::count(),
                'open_tickets' => Ticket::open()->count(),
                'pending_tickets' => Ticket::whereIn('status', ['pending', 'waiting_for_info', 'On Hold'])->count(),
                'overdue_tickets' => Ticket::open()->whereNotNull('resolution_deadline')->where('resolution_deadline', '<', now())->count(),
                'resolved_today' => Ticket::whereIn('status', ['Resolved', 'Closed'])->whereDate('resolved_at', Carbon::today())->count(),
                'last_updated' => now()->format('H:i:s')
            ],
            'recent_activities' => TicketActivity::orderBy('created_at', 'desc')->limit(5)->get()
        ]);
    }
}
