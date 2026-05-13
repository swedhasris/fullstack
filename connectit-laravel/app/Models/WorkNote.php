<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'user_name', 'ticket_id', 'ticket_number', 'session_id',
        'note_type', 'screenshot_url', 'screenshot_filename', 'screenshot_format',
        'screenshot_size_kb', 'ai_note', 'duration_seconds', 'duration_display',
    ];

    protected $casts = [
        'screenshot_size_kb' => 'integer',
        'duration_seconds'   => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }
}
