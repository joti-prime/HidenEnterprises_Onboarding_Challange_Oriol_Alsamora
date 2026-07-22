<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $is_recurring
 * @property mixed $code
 * @property mixed $discount_type
 * @property mixed $discount_amount
 * @property mixed $allowed_uses
 * @property mixed $applicable_products
 * @property mixed $expires_at
 * @property mixed $notes
 */
class Coupon extends Model
{
    use HasFactory;

    protected $table = 'coupons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'is_recurring',
        'discount_type',
        'discount_amount',
        'currency',
        'allowed_uses',
        'coupon_type',
        'applicable_products',
        'notes',
        'expires_at',
    ];

    protected $casts = [
        'applicable_products' => 'array',
        'expires_at' => 'datetime',
    ];

    public function isValid(): bool
    {
        if ($this->expires_at !== null) {
            if ($this->expires_at->isPast()) {
                return false;
            }
        }

        if ($this->allowed_uses == 0) {
            return false;
        }

        return true;
    }
}
