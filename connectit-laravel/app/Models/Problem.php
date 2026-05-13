<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Problem extends Model
{
    use HasFactory;

    protected $fillable = [
        'problem_number', 'title', 'description', 'status', 'priority',
        'category', 'root_cause', 'workaround', 'resolution',
        'assigned_to', 'assigned_to_name', 'reported_by', 'reported_by_name',
        'related_incidents', 'resolved_at', 'closed_at',
    ];

    protected $casts = [
        'resolved_at'       => 'datetime',
        'closed_at'         => 'datetime',
        'related_incidents' => 'integer',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'uid');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by', 'uid');
    }

    public static function generateNumber(): string
    {
        $latest = static::orderByDesc('id')->value('problem_number');
        if ($latest && preg_match('/PRB(\d+)/', $latest, $m)) {
            return 'PRB' . str_pad((int)$m[1] + 1, 7, '0', STR_PAD_LEFT);
        }
        return 'PRB1000001';
    }
}
