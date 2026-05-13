<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'status', 'owner', 'owner_name', 'location',
        'serial_number', 'model', 'manufacturer', 'purchase_date',
        'warranty_expiry', 'ip_address', 'description',
    ];

    protected $casts = [
        'purchase_date'   => 'date',
        'warranty_expiry' => 'date',
    ];

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner', 'uid');
    }

    /** Generate next asset number */
    public static function generateNumber(): string
    {
        $latest = static::orderByDesc('id')->value('id');
        return 'ASSET' . str_pad(($latest ?? 0) + 1, 6, '0', STR_PAD_LEFT);
    }
}
