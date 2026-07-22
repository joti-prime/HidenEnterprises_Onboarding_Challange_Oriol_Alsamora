<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @method static whereToken($token)
 */
class PasswordReset extends Model
{
    use HasFactory;

    protected $table = 'password_resets';

    public const UPDATED_AT = null;

    protected $primaryKey = 'email';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'token',
    ];

    public static function createPasswordResetToken($email)
    {
        if (PasswordReset::whereEmail($email)->exists()) {
            PasswordReset::whereEmail($email)->first()->delete();
        }

        $passwordReset = PasswordReset::updateOrCreate([
            'email' => $email,
            'token' => Str::random(60),
        ]);

        return $passwordReset->token;
    }
}
