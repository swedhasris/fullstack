<?php

namespace App\Http\Controllers;

use App\Models\Change;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Change::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('change_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $changes = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = [
            'draft'       => Change::where('state', 'Draft')->count(),
            'in_review'   => Change::where('state', 'In Review')->count(),
            'approved'    => Change::where('state', 'Approved')->count(),
            'implemented' => Change::where('state', 'Implemented')->count(),
        ];

        return view('changes.index', compact('changes', 'stats'));
    }

    public function show(Change $change)
    {
        return view('changes.show', compact('change'));
    }

    public function create()
    {
        $agents = User::whereIn('role', [UserRole::Agent->value, UserRole::Admin->value])
                      ->where('is_active', true)->orderBy('name')->get();
        return view('changes.create', compact('agents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:500',
            'description'        => 'nullable|string',
            'type'               => 'required|string|in:Normal,Standard,Emergency',
            'state'              => 'required|string',
            'risk'               => 'required|string|in:Low,Medium,High,Critical',
            'impact'             => 'nullable|string',
            'rollback_plan'      => 'nullable|string',
            'category'           => 'nullable|string|max:100',
            'affected_services'  => 'nullable|string',
            'assigned_to'        => 'nullable|string',
            'assigned_to_name'   => 'nullable|string',
            'planned_start_date' => 'nullable|date',
            'planned_end_date'   => 'nullable|date',
        ]);

        $user = Auth::user();
        $change = Change::create(array_merge($validated, [
            'change_number'  => Change::generateNumber(),
            'requester'      => $user->uid,
            'requester_name' => $user->name,
        ]));

        return redirect()->route('changes.show', $change)->with('success', "Change {$change->change_number} created.");
    }

    public function update(Request $request, Change $change)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:500',
            'description'        => 'nullable|string',
            'type'               => 'required|string',
            'state'              => 'required|string',
            'risk'               => 'required|string',
            'impact'             => 'nullable|string',
            'rollback_plan'      => 'nullable|string',
            'category'           => 'nullable|string|max:100',
            'affected_services'  => 'nullable|string',
            'assigned_to'        => 'nullable|string',
            'assigned_to_name'   => 'nullable|string',
            'planned_start_date' => 'nullable|date',
            'planned_end_date'   => 'nullable|date',
            'approval_status'    => 'nullable|string',
        ]);

        $change->update($validated);

        return back()->with('success', 'Change updated successfully.');
    }
}
