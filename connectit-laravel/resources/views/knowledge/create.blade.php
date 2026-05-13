@extends('layouts.app')
@section('title', 'New Article')
@section('page-title', 'New Knowledge Article')
@section('page-subtitle', 'Knowledge Base')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5"><h2 class="text-base font-bold">Create Knowledge Article</h2></div>
        <form method="POST" action="{{ route('knowledge.store') }}" class="p-5 space-y-4">
            @csrf
            @include('knowledge._form', ['article' => null])
            <div class="flex justify-between pt-4 border-t border-white/5">
                <a href="{{ route('knowledge.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Cancel
                </a>
                <button type="submit" class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-primary/90 transition-colors">
                    <i data-lucide="save" class="w-4 h-4"></i> Publish Article
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
