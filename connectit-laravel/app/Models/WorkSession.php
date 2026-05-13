<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'user_name', 'ticket_id', 'ticket_number',
        'start_time', 'stop_time', 'duration',
        'start_context', 'stop_context', 'ai_notes_start', 'ai_notes_stop', 'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'stop_time'  => 'datetime',
        'duration'   => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }
}
