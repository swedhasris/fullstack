@extends('layouts.app')
@section('title', $article->title)
@section('page-title', 'Knowledge Base')
@section('page-subtitle', $article->article_number)

@section('content')
<div class="p-6 max-w-4xl mx-auto space-y-4">
    <div class="flex items-center gap-2 text-[10px] text-gray-500 font-bold uppercase tracking-widest">
        <a href="{{ route('knowledge.index') }}" class="hover:text-primary transition-colors">Knowledge Base</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-primary">{{ $article->article_number }}</span>
    </div>

    <div class="bg-sn-card rounded-2xl p-6">
        <div class="flex items-start justify-between gap-4 mb-5">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-[9px] font-black px-2 py-0.5 rounded bg-primary/10 text-primary uppercase">{{ $article->category ?? 'General' }}</span>
                    <span class="text-[9px] font-black px-2 py-0.5 rounded {{ $article->status === 'Published' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">{{ $article->status }}</span>
                </div>
                <h1 class="text-xl font-bold text-white">{{ $article->title }}</h1>
                <div class="flex items-center gap-4 mt-2 text-[10px] text-gray-500">
                    <span>By {{ $article->author_name }}</span>
                    <span>{{ $article->views }} views</span>
                    <span>Updated {{ $article->updated_at->diffForHumans() }}</span>
                </div>
            </div>
            @if(auth()->user()->canManageTickets())
            <a href="{{ route('knowledge.edit', $article) }}" class="flex items-center gap-2 bg-white/5 text-gray-400 px-3 py-2 rounded-xl text-xs font-bold hover:bg-white/10 transition-colors shrink-0">
                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit
            </a>
            @endif
        </div>

        @if($article->summary)
        <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 mb-5">
            <p class="text-sm text-gray-300 leading-relaxed">{{ $article->summary }}</p>
        </div>
        @endif

        <div class="prose prose-invert prose-sm max-w-none text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $article->content }}</div>

        @if($article->tags)
        <div class="flex flex-wrap gap-2 mt-5 pt-5 border-t border-white/5">
            @foreach(explode(',', $article->tags) as $tag)
            <span class="text-[9px] font-bold px-2 py-1 rounded-lg bg-white/5 text-gray-500">{{ trim($tag) }}</span>
            @endforeach
        </div>
        @endif

        <div class="flex items-center gap-4 mt-5 pt-5 border-t border-white/5" x-data="{ voted: false }">
            <span class="text-xs text-gray-500">Was this helpful?</span>
            <button @click="!voted && vote(true)" :disabled="voted"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-500/10 text-green-400 text-xs font-bold hover:bg-green-500/20 disabled:opacity-50 transition-colors">
                <i data-lucide="thumbs-up" class="w-3.5 h-3.5"></i> Yes ({{ $article->helpful_count }})
            </button>
            <button @click="!voted && vote(false)" :disabled="voted"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-500/10 text-red-400 text-xs font-bold hover:bg-red-500/20 disabled:opacity-50 transition-colors">
                <i data-lucide="thumbs-down" class="w-3.5 h-3.5"></i> No ({{ $article->not_helpful_count }})
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('voteData', () => ({
        voted: false,
        async vote(helpful) {
            if (this.voted) return;
            this.voted = true;
            await window.api.post('/knowledge/{{ $article->id }}/helpful', { helpful });
        }
    }));
});
</script>
@endpush
