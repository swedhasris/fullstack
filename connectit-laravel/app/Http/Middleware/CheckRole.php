<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Usage: middleware('role:admin') or middleware('role:agent,admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRoleValue = $user->role->value;

        foreach ($roles as $role) {
            if ($userRoleValue === $role) {
                return $next($request);
            }
        }

        // Also allow higher roles
        $minLevel = PHP_INT_MAX;
        foreach ($roles as $role) {
            try {
                $minLevel = min($minLevel, UserRole::from($role)->level());
            } catch (\ValueError $e) {
                // ignore invalid role strings
            }
        }

        if ($user->role->level() >= $minLevel) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        abort(403, 'You do not have permission to access this page.');
    }
}
