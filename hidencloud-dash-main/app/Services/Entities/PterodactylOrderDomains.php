<?php

namespace App\Services\CloudFlare\Entities;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PterodactylOrderDomains
 *
 * This class represents the PterodactylOrderDomains model, which is used to manage the domains associated with a Pterodactyl order.
 *
 * @package App\Services\CloudFlare\Entities
 * @property int $id
 * @property int $order_id
 * @property array $domain_data
 */
class PterodactylOrderDomains extends Model
{

    protected $fillable = [
        'order_id',
        'domain_data',
    ];

    protected $casts = [
        'domain_data' => 'array',
    ];
    
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
