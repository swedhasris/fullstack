<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_number', 'title', 'category', 'subcategory', 'content', 'summary',
        'tags', 'views', 'rating', 'rating_count', 'helpful_count', 'not_helpful_count',
        'author', 'author_name', 'reviewer', 'reviewer_name', 'status', 'visibility',
        'version', 'published_at', 'archived_at',
    ];

    protected $casts = [
        'views'            => 'integer',
        'rating'           => 'float',
        'rating_count'     => 'integer',
        'helpful_count'    => 'integer',
        'not_helpful_count'=> 'integer',
        'version'          => 'integer',
        'published_at'     => 'datetime',
        'archived_at'      => 'datetime',
    ];

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author', 'uid');
    }

    public function reviewerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer', 'uid');
    }

    public static function generateNumber(): string
    {
        $latest = static::orderByDesc('id')->value('article_number');
        if ($latest && preg_match('/KB(\d+)/', $latest, $m)) {
            return 'KB' . str_pad((int)$m[1] + 1, 7, '0', STR_PAD_LEFT);
        }
        return 'KB1000001';
    }
}
