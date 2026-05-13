<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->role->canSystemSettings()) {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $settings = SystemSetting::all()->keyBy('setting_key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $user = Auth::user();

        foreach ($validated['settings'] as $key => $value) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                [
                    'setting_value' => $value,
                    'updated_by'    => $user->uid,
                    'updated_at'    => now(),
                ]
            );
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    /**
     * API: Get a setting value.
     */
    public function get(string $key)
    {
        $setting = SystemSetting::where('setting_key', $key)->first();
        return response()->json(['key' => $key, 'value' => $setting?->setting_value]);
    }
}
