@if($errors->any())
<div class="bg-red-900/30 border border-red-500/30 text-red-300 rounded-xl px-4 py-3 text-sm">
    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div>
    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Title *</label>
    <input type="text" name="title" value="{{ old('title', $article?->title) }}" required
           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
           placeholder="Article title">
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Category</label>
        <input type="text" name="category" value="{{ old('category', $article?->category) }}"
               class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
               placeholder="e.g. Hardware, Network">
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Status</label>
        <select name="status" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
            @foreach(['Draft', 'Published', 'Archived'] as $s)
            <option value="{{ $s }}" {{ old('status', $article?->status ?? 'Published') === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Visibility</label>
        <select name="visibility" class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
            @foreach(['Internal', 'Public'] as $v)
            <option value="{{ $v }}" {{ old('visibility', $article?->visibility ?? 'Internal') === $v ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Summary</label>
    <textarea name="summary" rows="2"
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600 resize-none"
              placeholder="Brief summary of the article">{{ old('summary', $article?->summary) }}</textarea>
</div>

<div>
    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Content *</label>
    <textarea name="content" rows="12" required
              class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600 resize-none font-mono"
              placeholder="Full article content...">{{ old('content', $article?->content) }}</textarea>
</div>

<div>
    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Tags (comma-separated)</label>
    <input type="text" name="tags" value="{{ old('tags', $article?->tags) }}"
           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
           placeholder="vpn, password, network, hardware">
</div>
