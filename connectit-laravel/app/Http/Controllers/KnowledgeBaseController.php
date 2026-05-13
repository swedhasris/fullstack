<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KnowledgeBaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = KnowledgeArticle::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'Published');
        }

        $articles   = $query->orderByDesc('views')->paginate(20)->withQueryString();
        $categories = KnowledgeArticle::distinct()->pluck('category')->filter()->sort()->values();

        return view('knowledge.index', compact('articles', 'categories'));
    }

    public function show(KnowledgeArticle $knowledgeArticle)
    {
        $knowledgeArticle->increment('views');
        return view('knowledge.show', ['article' => $knowledgeArticle]);
    }

    public function create()
    {
        return view('knowledge.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:500',
            'category'    => 'nullable|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'content'     => 'required|string',
            'summary'     => 'nullable|string',
            'tags'        => 'nullable|string',
            'status'      => 'required|string|in:Draft,Published,Archived',
            'visibility'  => 'required|string|in:Internal,Public',
        ]);

        $user = Auth::user();
        $article = KnowledgeArticle::create(array_merge($validated, [
            'article_number' => KnowledgeArticle::generateNumber(),
            'author'         => $user->uid,
            'author_name'    => $user->name,
            'published_at'   => $validated['status'] === 'Published' ? now() : null,
        ]));

        return redirect()->route('knowledge.show', $article)->with('success', 'Article created successfully.');
    }

    public function edit(KnowledgeArticle $knowledgeArticle)
    {
        return view('knowledge.edit', ['article' => $knowledgeArticle]);
    }

    public function update(Request $request, KnowledgeArticle $knowledgeArticle)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:500',
            'category'    => 'nullable|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'content'     => 'required|string',
            'summary'     => 'nullable|string',
            'tags'        => 'nullable|string',
            'status'      => 'required|string|in:Draft,Published,Archived',
            'visibility'  => 'required|string|in:Internal,Public',
        ]);

        if ($validated['status'] === 'Published' && !$knowledgeArticle->published_at) {
            $validated['published_at'] = now();
        }

        $knowledgeArticle->update($validated);

        return redirect()->route('knowledge.show', $knowledgeArticle)->with('success', 'Article updated successfully.');
    }

    public function helpful(Request $request, KnowledgeArticle $knowledgeArticle)
    {
        $field = $request->boolean('helpful') ? 'helpful_count' : 'not_helpful_count';
        $knowledgeArticle->increment($field);
        return response()->json(['success' => true]);
    }
}
