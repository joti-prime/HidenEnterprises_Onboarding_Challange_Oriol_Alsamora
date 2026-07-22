<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsedTwoFactorCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a code has been used
     */
    public static function isUsed($userId, $code): bool
    {
        return self::where('user_id', $userId)
            ->where('code', $code)
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }

    /**
     * Mark a code as used
     */
    public static function markAsUsed($userId, $code): void
    {
        self::create([
            'user_id' => $userId,
            'code' => $code,
            'expires_at' => Carbon::now()->addSeconds(90), // Expire after 90 seconds
        ]);
    }

    /**
     * Clean up expired codes
     */
    public static function cleanExpired(): int
    {
        return self::where('expires_at', '<=', Carbon::now())->delete();
    }
}