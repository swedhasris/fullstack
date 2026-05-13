<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->canManageUsers()) {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        })->except(['index']);
    }

    /**
     * List all users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $roles = UserRole::cases();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show create user form.
     */
    public function create()
    {
        $roles = UserRole::cases();
        return view('users.create', compact('roles'));
    }

    /**
     * Store new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8',
            'role'       => 'required|string|in:' . implode(',', array_column(UserRole::cases(), 'value')),
            'phone'      => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'uid'           => Str::uuid()->toString(),
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role'          => $validated['role'],
            'phone'         => $validated['phone'] ?? null,
            'department'    => $validated['department'] ?? null,
            'is_active'     => true,
            'provider'      => 'email',
        ]);

        if ($request->expectsJson()) {
            return response()->json($user, 201);
        }

        return redirect()->route('users.index')->with('success', "User {$user->name} created successfully.");
    }

    /**
     * Show edit user form.
     */
    public function edit(User $user)
    {
        $roles = UserRole::cases();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'role'       => 'required|string|in:' . implode(',', array_column(UserRole::cases(), 'value')),
            'phone'      => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
            'is_active'  => 'boolean',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $validated['password_hash'] = Hash::make($request->password);
        }

        $user->update($validated);

        if ($request->expectsJson()) {
            return response()->json($user);
        }

        return redirect()->route('users.index')->with('success', "User {$user->name} updated successfully.");
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$user->name} has been {$status}.");
    }

    /**
     * API: Get all agents/admins for assignment dropdowns.
     */
    public function agents()
    {
        $agents = User::whereIn('role', [
            UserRole::Agent->value,
            UserRole::SubAdmin->value,
            UserRole::Admin->value,
            UserRole::SuperAdmin->value,
            UserRole::UltraSuperAdmin->value,
        ])->where('is_active', true)->orderBy('name')->get(['uid', 'name', 'email', 'role', 'department']);

        return response()->json($agents);
    }
}
