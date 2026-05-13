<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'related_ticket_id',
        'related_entity_type', 'related_entity_id', 'is_read', 'read_at', 'created_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    public function relatedTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'related_ticket_id');
    }
}
