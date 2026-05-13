@extends('layouts.app')
@section('title', 'Knowledge Base')
@section('page-title', 'Knowledge Base')
@section('page-subtitle', 'Self-Service Articles')

@section('content')
<div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
        <p class="text-xs text-gray-500">{{ $articles->total() }} articles</p>
        @if(auth()->user()->canManageTickets())
        <a href="{{ route('knowledge.create') }}" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i> New Article
        </a>
        @endif
    </div>

    <div class="bg-sn-card rounded-2xl p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-500"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..."
                           class="w-full bg-white/5 border border-white/10 rounded-xl pl-9 pr-4 py-2 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600">
                </div>
            </div>
            <select name="category" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <button type="submit" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
                <i data-lucide="search" class="w-3.5 h-3.5"></i> Search
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($articles as $article)
        <a href="{{ route('knowledge.show', $article) }}" class="bg-sn-card rounded-2xl p-5 hover:border-primary/20 border border-transparent transition-all group">
            <div class="flex items-start justify-between mb-3">
                <span class="text-[9px] font-black px-2 py-0.5 rounded bg-primary/10 text-primary uppercase">{{ $article->category ?? 'General' }}</span>
                <div class="flex items-center gap-1 text-[9px] text-gray-500">
                    <i data-lucide="eye" class="w-3 h-3"></i> {{ $article->views }}
                </div>
            </div>
            <h3 class="text-sm font-bold text-white group-hover:text-primary transition-colors mb-2 line-clamp-2">{{ $article->title }}</h3>
            @if($article->summary)
            <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ $article->summary }}</p>
            @endif
            <div class="flex items-center justify-between text-[9px] text-gray-600">
                <span>{{ $article->author_name }}</span>
                <span>{{ $article->updated_at->diffForHumans() }}</span>
            </div>
        </a>
        @empty
        <div class="col-span-3 py-12 text-center text-gray-600">
            <i data-lucide="book-open" class="w-8 h-8 mx-auto mb-3 text-gray-700"></i>
            <p class="text-sm">No articles found</p>
        </div>
        @endforelse
    </div>

    @if($articles->hasPages())
    <div>{{ $articles->links('pagination::tailwind') }}</div>
    @endif
</div>
@endsection
