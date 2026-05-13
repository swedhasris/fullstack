<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Change extends Model
{
    use HasFactory;

    protected $fillable = [
        'change_number', 'title', 'description', 'type', 'state', 'risk',
        'impact', 'rollback_plan', 'requester', 'requester_name',
        'assigned_to', 'assigned_to_name', 'planned_start_date', 'planned_end_date',
        'actual_start_date', 'actual_end_date', 'category', 'affected_services',
        'approval_status',
    ];

    protected $casts = [
        'planned_start_date' => 'datetime',
        'planned_end_date'   => 'datetime',
        'actual_start_date'  => 'datetime',
        'actual_end_date'    => 'datetime',
    ];

    public function requesterUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester', 'uid');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'uid');
    }

    public static function generateNumber(): string
    {
        $latest = static::orderByDesc('id')->value('change_number');
        if ($latest && preg_match('/CHG(\d+)/', $latest, $m)) {
            return 'CHG' . str_pad((int)$m[1] + 1, 7, '0', STR_PAD_LEFT);
        }
        return 'CHG1000001';
    }
}
