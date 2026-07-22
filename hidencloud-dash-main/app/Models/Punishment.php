<?php

namespace App\Models;

use App\Events;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Punishment extends Model
{
    use HasFactory;

    protected $table = 'punishments';

    protected $fillable = [
        'user_id',
        'staff_id',
        'type',
        'reason',
        'ip_address',
        'expires_at',
        'metadata',
        'source',
        'external_reference',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Define dispatchable events
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => Events\Punishment\PunishmentCreated::class,
        'updated' => Events\Punishment\PunishmentUpdated::class,
        'deleted' => Events\Punishment\PunishmentDeleted::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unban()
    {
        $this->type = 'unbanned';
        $this->expires_at = now();
        $this->save();
    }

    public static function hasActiveBans()
    {
        // ipban removed: dash sits behind Cloudflare, request IPs are CF edges so an IP ban
        // would block many unrelated users. Only user-scoped 'ban' is checked.
        $user = auth()->user();

        if ($user) {
            $ban = Punishment::where('type', 'ban')->where('user_id', $user->id)->latest('created_at')->first();

            if ($ban) {
                if (isset($ban->expires_at) and $ban->expires_at->isPast()) {
                    return false;
                }

                return true;
            }
        }

        return false;
    }

    public static function getActiveBan()
    {
        $user = auth()->user();
        if ($user) {
            $ban = Punishment::where('type', 'ban')->where('user_id', $user->id)->latest('created_at')->first();

            if ($ban) {
                if (isset($ban->expires_at) and $ban->expires_at->isPast()) {
                    return false;
                }

                return $ban;
            }
        }

        return false;
    }
}
