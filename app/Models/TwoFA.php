<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\UsedTwoFactorCode;

class TwoFA extends Model
{
    protected $table = 'user_2fa';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'key',
        'recovery_codes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'key',
        'recovery_codes',
    ];

    protected $casts = [
        'recovery_codes' => 'array',
        'session_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Validate 2FA code
     *
     * @param  Google2FA  $google2fa  Instance of Google2FA library
     * @param  string  $code  The 2FA code to validate
     * @param  bool  $checkReuse  Whether to check for code reuse
     * @return bool|string True if valid, 'reused' if code was reused, false if invalid
     */
    public function validate(string $code, bool $checkReuse = true)
    {
        // Check if code was already used
        if ($checkReuse && UsedTwoFactorCode::isUsed($this->user_id, $code)) {
            return 'reused';
        }

        $google2fa = app(\PragmaRX\Google2FALaravel\Google2FA::class);

        $isValid = $google2fa->verifyKey($this->key, $code);

        // Mark code as used if valid and checking reuse
        if ($isValid && $checkReuse) {
            UsedTwoFactorCode::markAsUsed($this->user_id, $code);
        }

        return $isValid ? true : false;
    }

    /**
     * Invalidate 2FA session
     *
     * @var array<int, string>
     */
    public function invalidate()
    {
        $this->session_expires_at = Carbon::now()->subDays(5);
        $this->save();
    }

    /**
     * Require user to validate 2FA
     *
     * This function is similar to the invalidate but can be called at different times
     *
     * @var array<int, string>
     */
    public function require()
    {
        $this->session_expires_at = Carbon::now()->subDays(5);
        $this->save();
    }

    /**
     * Disable 2FA
     *
     * @var array<int, string>
     */
    public function disable()
    {
        $this->delete();
    }

    public function setKeyAttribute($value)
    {
        $this->attributes['key'] = encrypt($value);
    }

    public function setRecoveryCodesAttribute($value)
    {
        $this->attributes['recovery_codes'] = encrypt($value);
    }

    public function getKeyAttribute($value)
    {
        return decrypt($value);
    }

    public function getRecoveryCodesAttribute($value)
    {
        return decrypt($value);
    }
}
