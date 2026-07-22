<?php

namespace App\Models;

use App\Events;
use App\Traits\Models\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOauth extends Model
{
    use HasSettings;

    protected $table = 'user_oauths';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'driver',
        'email',
        'data',
        'external_profile',
        'display_on_profile',
    ];

    protected $casts = [
        'data' => 'object',
    ];

    /**
     * Define dispatchable events
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => Events\Oauth\OauthConnected::class,
        'updated' => Events\Oauth\OauthUpdated::class,
        'deleted' => Events\Oauth\OauthDisconnected::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
