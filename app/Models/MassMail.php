<?php

namespace App\Models;

use App\Jobs\SendEmail;
use App\Traits\Models\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class MassMail extends Model
{
    use HasSettings;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'audience',
        'subject',
        'content',
        'button_text',
        'button_url',
        'attachment',
        'email_theme',
        'status',
        'repeat',
        'sent_count',
        'custom_selection',
        'scheduled_at',
        'last_completed_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'custom_selection' => 'array',
        'scheduled_at' => 'datetime',
        'last_completed_at' => 'datetime',
    ];

    public function audience()
    {
        return MassMail::getMailAudience($this->audience, $this->custom_selection ?? []);
    }

    public static function getMailAudience(string $audience, array $customSelection = [])
    {
        if($audience === 'all_users') {
            return User::all();
        }

        if($audience === 'has_orders') {
            return User::has('orders')->get();
        }

        if($audience === 'active_orders') {
            return User::whereHas('orders', function ($query) {
                $query->where('status', 'active');
            })->get();
        }

        if($audience === 'inactive_orders') {
            return User::whereHas('orders', function ($query) {
                $query->where('status', '!=', 'active');
            })->get();
        }

        if($audience === 'suspended_orders') {
            return User::whereHas('orders', function ($query) {
                $query->where('status', 'suspended');
            })->get();
        }

        if($audience === 'terminated_orders') {
            return User::whereHas('orders', function ($query) {
                $query->where('status', 'terminated_orders');
            })->get();
        }

        if($audience === 'no_orders') {
            return User::whereDoesntHave('orders')->get();
        }

        if($audience === 'subscribed') {
            return User::where('is_subscribed', true)->get();
        }

        // if audience starts with service_ remove the service_ prefix and return the users with that service
        if(strpos($audience, 'service_') === 0) {
            $service = substr($audience, 8);
            return User::whereHas('orders', function ($query) use ($service) {
                $query->where('service', $service);
            })->get();
        }

        if($audience === 'custom_selection') {
            return User::whereIn('id', $customSelection)->get();
        }

        return User::query()->where('id', 0)->get();
    }
}
