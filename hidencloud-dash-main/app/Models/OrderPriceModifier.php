<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Scopes\ActiveModifierScope;

class OrderPriceModifier extends Model
{
    use HasFactory;

    protected $table = 'order_price_modifiers';

    protected $fillable = [
        'order_id',
        'description',
        'type',
        'key',
        'value',
        'base_price',
        'daily_price',
        'cancellation_fee',
        'upgrade_fee',
        'data',
        'is_active',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'data' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ActiveModifierScope);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeWithoutActiveModifierScope($query)
    {
        return $query->withoutGlobalScope(ActiveModifierScope::class);
    }

    public function isActive(): bool
    {
        // Check if the modifier is active based on the start and end date if they are set
        if ($this->start_date && $this->end_date) {
            return now()->between($this->start_date, $this->end_date);
        }

        if($this->start_date) {
            return now()->gte($this->start_date);
        }

        if($this->end_date) {
            return now()->lte($this->end_date);
        }

        return $this->is_active;
    }
}
