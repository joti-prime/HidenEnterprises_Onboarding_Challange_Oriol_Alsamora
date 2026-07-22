<?php

namespace App\Models;

use App\Events;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceAccount extends Model
{
    use HasFactory;

    protected $table = 'service_accounts';

    protected $fillable = [
        'user_id',
        'order_id',
        'service',
        'external_id',
        'data',
        'username',
        'password',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected $hidden = [
        'data',
        'password',
    ];

    /**
     * Define dispatchable events
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => Events\ExternalAccounts\AccountCreated::class,
        'updated' => Events\ExternalAccounts\AccountUpdated::class,
        'deleted' => Events\ExternalAccounts\AccountDeleted::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
