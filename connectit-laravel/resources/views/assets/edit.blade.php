@extends('layouts.app')
@section('title', 'Edit Asset')
@section('page-title', 'Edit Asset')
@section('page-subtitle', 'CMDB')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5"><h2 class="text-base font-bold">Edit: {{ $asset->name }}</h2></div>
        <form method="POST" action="{{ route('assets.update', $asset) }}" class="p-5 space-y-4">
            @csrf @method('PUT')
            @include('assets._form', ['asset' => $asset, 'users' => $users])
            <div class="flex justify-between pt-4 border-t border-white/5">
                <a href="{{ route('assets.show', $asset) }}" class="flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Cancel
                </a>
                <button type="submit" class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-primary/90 transition-colors">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
