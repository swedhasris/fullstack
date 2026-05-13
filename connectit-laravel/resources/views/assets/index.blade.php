@extends('layouts.app')
@section('title', 'CMDB / Assets')
@section('page-title', 'CMDB — Configuration Management')
@section('page-subtitle', 'Asset Inventory')

@section('content')
<div class="p-6 space-y-4">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach([
            ['Total Assets',  $stats['total'],       'server',       'text-blue-400'],
            ['Operational',   $stats['operational'], 'check-circle', 'text-green-400'],
            ['Maintenance',   $stats['maintenance'], 'tool',         'text-amber-400'],
            ['Retired',       $stats['retired'],     'archive',      'text-gray-400'],
        ] as [$label, $val, $icon, $color])
        <div class="bg-sn-card p-4 rounded-xl flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center shrink-0">
                <i data-lucide="{{ $icon }}" class="w-4 h-4 {{ $color }}"></i>
            </div>
            <div>
                <div class="text-xl font-black {{ $color }}">{{ $val }}</div>
                <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest">{{ $label }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="bg-sn-card rounded-2xl p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-500"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, serial, IP..."
                           class="w-full bg-white/5 border border-white/10 rounded-xl pl-9 pr-4 py-2 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600">
                </div>
            </div>
            <select name="type" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                <option value="">All Types</option>
                @foreach($types as $type)
                <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            <select name="status" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                <option value="">All Statuses</option>
                @foreach(['Operational', 'Maintenance', 'Retired', 'Disposed'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <button type="submit" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter
            </button>
            <a href="{{ route('assets.index') }}" class="flex items-center gap-2 bg-white/5 text-gray-400 px-4 py-2 rounded-xl text-xs font-bold hover:bg-white/10 transition-colors">
                <i data-lucide="x" class="w-3.5 h-3.5"></i> Clear
            </a>
            @if(auth()->user()->canManageTickets())
            <a href="{{ route('assets.create') }}" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors ml-auto">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Asset
            </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5">
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Owner</th>
                        <th class="px-5 py-3">Location</th>
                        <th class="px-5 py-3">Serial #</th>
                        <th class="px-5 py-3">IP Address</th>
                        <th class="px-5 py-3">Warranty</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors group">
                        <td class="px-5 py-3">
                            <a href="{{ route('assets.show', $asset) }}" class="text-sm font-medium text-white hover:text-primary transition-colors">{{ $asset->name }}</a>
                            @if($asset->model)<div class="text-[9px] text-gray-600">{{ $asset->manufacturer }} {{ $asset->model }}</div>@endif
                        </td>
                        <td class="px-5 py-3"><span class="text-[9px] font-black px-2 py-0.5 rounded bg-white/5 text-gray-300">{{ $asset->type }}</span></td>
                        <td class="px-5 py-3">
                            @php $sc = ['Operational' => 'bg-green-500/20 text-green-400', 'Maintenance' => 'bg-amber-500/20 text-amber-400', 'Retired' => 'bg-gray-500/20 text-gray-400', 'Disposed' => 'bg-red-500/20 text-red-400']; @endphp
                            <span class="text-[9px] font-black px-2 py-0.5 rounded {{ $sc[$asset->status] ?? 'bg-gray-500/20 text-gray-400' }}">{{ $asset->status }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $asset->owner_name ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $asset->location ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs font-mono text-gray-400">{{ $asset->serial_number ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs font-mono text-gray-400">{{ $asset->ip_address ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if($asset->warranty_expiry)
                            <span class="text-[9px] {{ $asset->warranty_expiry->isPast() ? 'text-red-400' : 'text-green-400' }}">
                                {{ $asset->warranty_expiry->format('M Y') }}
                            </span>
                            @else
                            <span class="text-[9px] text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('assets.show', $asset) }}" class="p-1.5 hover:bg-white/10 rounded-lg transition-colors">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-gray-400"></i>
                                </a>
                                @if(auth()->user()->canManageTickets())
                                <a href="{{ route('assets.edit', $asset) }}" class="p-1.5 hover:bg-white/10 rounded-lg transition-colors">
                                    <i data-lucide="edit-2" class="w-3.5 h-3.5 text-gray-400"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-5 py-12 text-center text-gray-600 text-sm">No assets found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())
        <div class="p-4 border-t border-white/5">{{ $assets->links('pagination::tailwind') }}</div>
        @endif
    </div>
</div>
@endsection
