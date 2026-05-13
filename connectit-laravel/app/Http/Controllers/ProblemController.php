<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProblemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Problem::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('problem_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $problems = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = [
            'open'       => Problem::where('status', 'Open')->count(),
            'in_progress'=> Problem::where('status', 'In Progress')->count(),
            'resolved'   => Problem::where('status', 'Resolved')->count(),
        ];

        return view('problems.index', compact('problems', 'stats'));
    }

    public function show(Problem $problem)
    {
        return view('problems.show', compact('problem'));
    }

    public function create()
    {
        $agents = User::whereIn('role', [UserRole::Agent->value, UserRole::Admin->value])
                      ->where('is_active', true)->orderBy('name')->get();
        return view('problems.create', compact('agents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:500',
            'description'      => 'nullable|string',
            'status'           => 'required|string',
            'priority'         => 'required|string',
            'category'         => 'nullable|string|max:100',
            'root_cause'       => 'nullable|string',
            'workaround'       => 'nullable|string',
            'assigned_to'      => 'nullable|string',
            'assigned_to_name' => 'nullable|string',
        ]);

        $user = Auth::user();
        $problem = Problem::create(array_merge($validated, [
            'problem_number'   => Problem::generateNumber(),
            'reported_by'      => $user->uid,
            'reported_by_name' => $user->name,
        ]));

        return redirect()->route('problems.show', $problem)->with('success', "Problem {$problem->problem_number} created.");
    }

    public function update(Request $request, Problem $problem)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:500',
            'description'      => 'nullable|string',
            'status'           => 'required|string',
            'priority'         => 'required|string',
            'category'         => 'nullable|string|max:100',
            'root_cause'       => 'nullable|string',
            'workaround'       => 'nullable|string',
            'resolution'       => 'nullable|string',
            'assigned_to'      => 'nullable|string',
            'assigned_to_name' => 'nullable|string',
        ]);

        if ($validated['status'] === 'Resolved' && !$problem->resolved_at) {
            $validated['resolved_at'] = now();
        }

        $problem->update($validated);

        return back()->with('success', 'Problem updated successfully.');
    }
}
