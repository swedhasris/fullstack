@extends('layouts.app')
@section('title', $asset->name)
@section('page-title', 'Asset Detail')
@section('page-subtitle', 'CMDB · ' . $asset->type)

@section('content')
<div class="p-6 max-w-4xl mx-auto space-y-4">
    <div class="flex items-center gap-2 text-[10px] text-gray-500 font-bold uppercase tracking-widest">
        <a href="{{ route('assets.index') }}" class="hover:text-primary transition-colors">CMDB</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-primary">{{ $asset->name }}</span>
    </div>

    <div class="bg-sn-card rounded-2xl p-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-white">{{ $asset->name }}</h2>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-[9px] font-black px-2 py-0.5 rounded bg-white/5 text-gray-300">{{ $asset->type }}</span>
                    @php $sc = ['Operational' => 'bg-green-500/20 text-green-400', 'Maintenance' => 'bg-amber-500/20 text-amber-400', 'Retired' => 'bg-gray-500/20 text-gray-400']; @endphp
                    <span class="text-[9px] font-black px-2 py-0.5 rounded {{ $sc[$asset->status] ?? 'bg-gray-500/20 text-gray-400' }}">{{ $asset->status }}</span>
                </div>
            </div>
            @if(auth()->user()->canManageTickets())
            <a href="{{ route('assets.edit', $asset) }}" class="flex items-center gap-2 bg-white/5 text-gray-400 px-3 py-2 rounded-xl text-xs font-bold hover:bg-white/10 transition-colors">
                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit
            </a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <dl class="space-y-3">
                <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-3">Hardware Details</div>
                @foreach([
                    ['Manufacturer', $asset->manufacturer],
                    ['Model',        $asset->model],
                    ['Serial Number',$asset->serial_number],
                    ['IP Address',   $asset->ip_address],
                ] as [$label, $value])
                @if($value)
                <div class="flex justify-between gap-3">
                    <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest">{{ $label }}</dt>
                    <dd class="text-xs text-gray-300 font-mono">{{ $value }}</dd>
                </div>
                @endif
                @endforeach
            </dl>
            <dl class="space-y-3">
                <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-3">Ownership & Location</div>
                @foreach([
                    ['Owner',          $asset->owner_name],
                    ['Location',       $asset->location],
                    ['Purchase Date',  $asset->purchase_date?->format('M d, Y')],
                    ['Warranty Expiry',$asset->warranty_expiry?->format('M d, Y')],
                ] as [$label, $value])
                @if($value)
                <div class="flex justify-between gap-3">
                    <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest">{{ $label }}</dt>
                    <dd class="text-xs text-gray-300">{{ $value }}</dd>
                </div>
                @endif
                @endforeach
            </dl>
        </div>

        @if($asset->description)
        <div class="mt-5 pt-5 border-t border-white/5">
            <div class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Description</div>
            <p class="text-sm text-gray-300 leading-relaxed">{{ $asset->description }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
