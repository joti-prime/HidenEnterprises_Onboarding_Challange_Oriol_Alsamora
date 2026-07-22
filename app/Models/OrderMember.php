<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMember extends Model
{
    use HasFactory;

    protected $table = 'order_members';

    protected $fillable = [
        'order_id',
        'inviter_id',
        'user_id',
        'email',
        'status',
        'is_admin',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function getEmailAttribute()
    {
        return $this->user->email ?? $this->attributes['email'] ?? null;
    }

    public function hasPerm(string $routeOrPage): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return collect($this->permissions ?? [])->contains(function ($value, $key) use ($routeOrPage) {
            if ($value == 'contains'){
                return str_contains($routeOrPage, $key);
            }

            return $key === $routeOrPage;
        });
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
