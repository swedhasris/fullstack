<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'timesheet_id', 'user_id', 'entry_date', 'task', 'hours_worked',
        'description', 'short_description', 'start_time', 'end_time',
        'deduct', 'work_type', 'billable', 'status', 'elapsed_seconds',
    ];

    protected $casts = [
        'entry_date'     => 'date',
        'hours_worked'   => 'float',
        'deduct'         => 'float',
        'elapsed_seconds'=> 'integer',
    ];

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }
}
