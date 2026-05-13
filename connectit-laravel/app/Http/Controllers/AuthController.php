<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact your administrator.'],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $user->update(['last_login' => now()]);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show registration form.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Handle registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'uid'           => Str::uuid()->toString(),
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role'          => UserRole::User,
            'is_active'     => true,
            'provider'      => 'email',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Show profile page.
     */
    public function profile()
    {
        return view('auth.profile', ['user' => Auth::user()]);
    }

    /**
     * Update profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'nullable|string|max:50',
            'department' => 'nullable|string|max:100',
        ]);

        $user->update($validated);

        if ($request->filled('password')) {
            $request->validate([
                'password'              => 'required|string|min:8|confirmed',
                'current_password'      => 'required|string',
            ]);

            if (!Hash::check($request->current_password, $user->password_hash)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            $user->update(['password_hash' => Hash::make($request->password)]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}
