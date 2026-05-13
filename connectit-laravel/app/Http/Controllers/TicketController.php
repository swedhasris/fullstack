<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\SlaPolicy;
use App\Services\TicketService;
use App\Services\AiService;
use App\Services\OmniChannelService;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketImpact;
use App\Enums\TicketUrgency;
use App\Enums\TicketChannel;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService $ticketService,
        protected AiService $aiService
    ) {
        $this->middleware('auth');
    }

    /**
     * List tickets with filters.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::query()->with(['assignee']);

        // Role-based visibility
        if (!$user->canViewAllTickets()) {
            $query->where(function ($q) use ($user) {
                $q->where('caller_user_id', $user->uid)
                  ->orWhere('created_by', $user->uid);
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhere('caller', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $tickets = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $statuses   = TicketStatus::cases();
        $priorities = TicketPriority::cases();
        $agents     = User::whereIn('role', [UserRole::Agent->value, UserRole::Admin->value, UserRole::SubAdmin->value])
                          ->where('is_active', true)->orderBy('name')->get();

        // Stats for header
        $stats = [
            'open'     => Ticket::open()->count(),
            'critical' => Ticket::where('priority', TicketPriority::Critical->value)->open()->count(),
            'overdue'  => Ticket::open()->whereNotNull('resolution_deadline')->where('resolution_deadline', '<', now())->count(),
            'my_open'  => Ticket::where('assigned_to', $user->uid)->open()->count(),
        ];

        return view('tickets.index', compact('tickets', 'statuses', 'priorities', 'agents', 'stats'));
    }

    /**
     * Show ticket detail.
     */
    public function show(Ticket $ticket)
    {
        $user = Auth::user();

        // Access control
        if (!$user->canViewAllTickets()) {
            if ($ticket->caller_user_id !== $user->uid && $ticket->created_by !== $user->uid) {
                abort(403, 'You do not have permission to view this ticket.');
            }
        }

        $ticket->load(['activities', 'history', 'comments', 'approvals', 'assignee', 'creator', 'children']);

        $agents = User::whereIn('role', [UserRole::Agent->value, UserRole::Admin->value, UserRole::SubAdmin->value])
                      ->where('is_active', true)->orderBy('name')->get();

        $statuses = TicketStatus::cases();
        $allowedTransitions = $ticket->status->allowedTransitions();

        return view('tickets.show', compact('ticket', 'agents', 'statuses', 'allowedTransitions'));
    }

    /**
     * Show create ticket form.
     */
    public function create()
    {
        $impacts   = TicketImpact::cases();
        $urgencies = TicketUrgency::cases();
        $channels  = TicketChannel::cases();
        $agents    = User::whereIn('role', [UserRole::Agent->value, UserRole::Admin->value, UserRole::SubAdmin->value])
                         ->where('is_active', true)->orderBy('name')->get();
        $users     = User::where('is_active', true)->orderBy('name')->get();

        return view('tickets.create', compact('impacts', 'urgencies', 'channels', 'agents', 'users'));
    }

    /**
     * Store a new ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:500',
            'caller'           => 'required|string|max:255',
            'caller_email'     => 'nullable|email',
            'caller_user_id'   => 'nullable|string',
            'affected_user'    => 'nullable|string',
            'impact'           => 'required|string',
            'urgency'          => 'required|string',
            'category'         => 'nullable|string|max:100',
            'subcategory'      => 'nullable|string|max:100',
            'service'          => 'nullable|string|max:100',
            'service_offering' => 'nullable|string|max:100',
            'cmdb_item'        => 'nullable|string|max:100',
            'description'      => 'nullable|string',
            'channel'          => 'nullable|string',
            'assignment_group' => 'nullable|string|max:100',
            'assigned_to'      => 'nullable|string',
            'assigned_to_name' => 'nullable|string',
        ]);

        $ticket = $this->ticketService->createTicket($validated, Auth::user());

        if ($request->expectsJson()) {
            return response()->json($ticket->load('activities'), 201);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', "Ticket {$ticket->ticket_number} created successfully.");
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        $newStatus = TicketStatus::from($request->status);
        $updatedTicket = $this->ticketService->updateStatus($ticket, $newStatus, $request->reason, Auth::user());

        if ($request->expectsJson()) {
            return response()->json($updatedTicket);
        }

        return back()->with('success', "Ticket status updated to {$newStatus->value}.");
    }

    /**
     * Assign ticket.
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'assigned_to'      => 'nullable|string',
            'assigned_to_name' => 'nullable|string',
            'assignment_group' => 'nullable|string',
        ]);

        $updatedTicket = $this->ticketService->assignTicket($ticket, $validated, Auth::user());

        if ($request->expectsJson()) {
            return response()->json($updatedTicket);
        }

        return back()->with('success', 'Ticket assigned successfully.');
    }

    /**
     * Add comment or work note.
     */
    public function comment(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message'     => 'required|string',
            'is_internal' => 'boolean',
        ]);

        $activity = $this->ticketService->addComment(
            $ticket,
            $request->message,
            $request->boolean('is_internal'),
            Auth::user()
        );

        if ($request->expectsJson()) {
            return response()->json($activity, 201);
        }

        return back()->with('success', 'Comment added successfully.');
    }

    /**
     * Resolve ticket.
     */
    public function resolve(Request $request, Ticket $ticket)
    {
        $request->validate([
            'resolution_code'    => 'required|string|max:100',
            'resolution_notes'   => 'required|string',
            'resolution_method'  => 'nullable|string|max:100',
            'closure_reason'     => 'nullable|string|max:100',
        ]);

        $ticket->update([
            'resolution_code'   => $request->resolution_code,
            'resolution_notes'  => $request->resolution_notes,
            'resolution_method' => $request->resolution_method,
            'closure_reason'    => $request->closure_reason,
        ]);

        $this->ticketService->updateStatus($ticket, TicketStatus::Resolved, $request->resolution_notes, Auth::user());

        if ($request->expectsJson()) {
            return response()->json($ticket->fresh());
        }

        return back()->with('success', 'Ticket resolved successfully.');
    }

    /**
     * AI Suggestion.
     */
    public function suggest(Request $request)
    {
        $request->validate(['text' => 'required|string']);
        $suggestion = $this->aiService->getSuggestion($request->text);
        return response()->json(['suggestion' => $suggestion]);
    }

    /**
     * AI Chat with Kiru.
     */
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string']);
        $response = $this->aiService->chat($request->message, $request->history ?? []);
        return response()->json(['response' => $response]);
    }

    /**
     * Trigger manual notification.
     */
    public function notify(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'type'      => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($request->ticket_id);

        match ($request->type) {
            'created'  => event(new \App\Events\TicketCreated($ticket)),
            'assigned' => event(new \App\Events\TicketAssigned($ticket)),
            'resolved' => event(new \App\Events\TicketResolved($ticket)),
            'commented'=> event(new \App\Events\CommentAdded($ticket, $request->message ?? 'New update added.')),
            default    => null,
        };

        return response()->json(['status' => "Notification event '{$request->type}' dispatched"]);
    }

    /**
     * Get ticket activities (API).
     */
    public function activities(Ticket $ticket)
    {
        $activities = $ticket->activities()->orderBy('created_at')->get();
        return response()->json($activities);
    }

    /**
     * Log activity (API bridge for frontend).
     */
    public function logActivity(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        $request->validate([
            'activity_type'   => 'required|string',
            'visibility_type' => 'nullable|string',
            'message'         => 'required|string',
            'channel'         => 'nullable|string',
            'metadata_json'   => 'nullable|array',
        ]);

        $activity = \App\Models\TicketActivity::create([
            'ticket_id'       => $ticket->id,
            'activity_type'   => $request->activity_type,
            'visibility_type' => $request->visibility_type ?? 'public',
            'channel'         => $request->channel ?? 'portal',
            'message'         => $request->message,
            'metadata_json'   => $request->metadata_json,
            'created_by'      => Auth::user()?->uid ?? 'system',
            'created_by_name' => Auth::user()?->name ?? 'System',
            'created_at'      => now(),
        ]);

        return response()->json($activity, 201);
    }
}
