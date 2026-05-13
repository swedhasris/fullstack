<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'week_start', 'week_end', 'status', 'total_hours', 'submitted_at',
    ];

    protected $casts = [
        'week_start'   => 'date',
        'week_end'     => 'date',
        'total_hours'  => 'float',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    public function timeCards(): HasMany
    {
        return $this->hasMany(TimeCard::class)->orderBy('entry_date');
    }
}
