<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserIp
 *
 * @property int $id
 * @property int $user_id
 * @property string $ip_address
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserIp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserIp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserIp query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserIp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserIp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserIp whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserIp whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserIp whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserIp extends Model
{
    protected $table = 'user_ips';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasDuplicateIp($user_id)
    {
        // Get the user associated with this IP address
        $user = User::find($user_id);

        // Check if any other users have the same IP address
        $count = UserIp::where('ip_address', $this->ip_address)
            ->where('user_id', '<>', $user->id);

        return ($count->exists()) ? User::find($count->first()->user_id) : null;
    }
}
