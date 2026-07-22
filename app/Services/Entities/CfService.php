<?php

namespace App\Services\CloudFlare\Entities;

use App\Models\Package;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CfService extends Model
{

    protected $fillable = [
        'package_id',
        'zones_ids',
    ];

    protected $casts = [
        'zones_ids' => 'array',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public static function getDomainsByPackage($package_id): array
    {
        $zones = self::where('package_id', $package_id)->first();
        $domainList = CfHelper::getDomainsList();
        return $domainList->filter(fn($value, $key) => in_array($key, $zones->zones_ids))->toArray();
    }

    public static function getOrderSubdomain($order_id, $service = 'pterodactyl'): PterodactylOrderDomains
    {
        return PterodactylOrderDomains::where('order_id', $order_id)->first() ?? new PterodactylOrderDomains();
    }
}