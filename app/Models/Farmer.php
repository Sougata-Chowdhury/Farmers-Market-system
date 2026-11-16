<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'farm_name',
        'location',
        'bio',
        'bank_name',
        'branch',
        'account_no',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'farmer_id', 'user_id');
    }

    public function earnings(): float
    {
        return (float) $this->sales()->sum('line_total');
    }
}
