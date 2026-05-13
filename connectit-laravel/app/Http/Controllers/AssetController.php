<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assets = $query->orderBy('name')->paginate(25)->withQueryString();
        $types  = Asset::distinct()->pluck('type')->filter()->sort()->values();

        $stats = [
            'total'       => Asset::count(),
            'operational' => Asset::where('status', 'Operational')->count(),
            'maintenance' => Asset::where('status', 'Maintenance')->count(),
            'retired'     => Asset::where('status', 'Retired')->count(),
        ];

        return view('assets.index', compact('assets', 'types', 'stats'));
    }

    public function show(Asset $asset)
    {
        return view('assets.show', compact('asset'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('assets.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|string|max:50',
            'status'          => 'required|string|max:50',
            'owner'           => 'nullable|string',
            'owner_name'      => 'nullable|string',
            'location'        => 'nullable|string|max:255',
            'serial_number'   => 'nullable|string|max:255',
            'model'           => 'nullable|string|max:255',
            'manufacturer'    => 'nullable|string|max:255',
            'purchase_date'   => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'ip_address'      => 'nullable|string|max:50',
            'description'     => 'nullable|string',
        ]);

        $asset = Asset::create($validated);

        if ($request->expectsJson()) {
            return response()->json($asset, 201);
        }

        return redirect()->route('assets.show', $asset)->with('success', 'Asset created successfully.');
    }

    public function edit(Asset $asset)
    {
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('assets.edit', compact('asset', 'users'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|string|max:50',
            'status'          => 'required|string|max:50',
            'owner'           => 'nullable|string',
            'owner_name'      => 'nullable|string',
            'location'        => 'nullable|string|max:255',
            'serial_number'   => 'nullable|string|max:255',
            'model'           => 'nullable|string|max:255',
            'manufacturer'    => 'nullable|string|max:255',
            'purchase_date'   => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'ip_address'      => 'nullable|string|max:50',
            'description'     => 'nullable|string',
        ]);

        $asset->update($validated);

        if ($request->expectsJson()) {
            return response()->json($asset);
        }

        return redirect()->route('assets.show', $asset)->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset deleted.');
    }
}
