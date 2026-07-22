<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\HigherOrderCollectionProxy;

/**
 * App\Models\PackagePrice
 *
 * @property int $id
 * @property int $package_id
 * @property string|null $period
 * @property int|null $frequency
 * @property float $price
 * @property float $renewal_price
 * @property float $setup_fee
 * @property float $cancellation_fee
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property mixed $data
 * @property HigherOrderBuilderProxy|HigherOrderCollectionProxy|mixed $is_active
 *
 * @method static Builder|PackagePrice newModelQuery()
 * @method static Builder|PackagePrice newQuery()
 * @method static Builder|PackagePrice onlyTrashed()
 * @method static Builder|PackagePrice query()
 * @method static Builder|PackagePrice whereCancellationFee($value)
 * @method static Builder|PackagePrice whereCreatedAt($value)
 * @method static Builder|PackagePrice whereDeletedAt($value)
 * @method static Builder|PackagePrice whereFrequency($value)
 * @method static Builder|PackagePrice whereId($value)
 * @method static Builder|PackagePrice wherePackageId($value)
 * @method static Builder|PackagePrice wherePeriod($value)
 * @method static Builder|PackagePrice wherePrice($value)
 * @method static Builder|PackagePrice whereRenewalPrice($value)
 * @method static Builder|PackagePrice whereSetupFee($value)
 * @method static Builder|PackagePrice whereUpdatedAt($value)
 * @method static Builder|PackagePrice withTrashed()
 * @method static Builder|PackagePrice withoutTrashed()
 *
 * @mixin \Eloquent
 */
class PackagePrice extends Model
{
    use HasFactory;

    protected $table = 'package_prices';

    protected $casts = [
        'data' => 'array',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function totalPrice(): float
    {
        return $this->price + $this->setup_fee;
    }

    public function period(): string
    {
        if ($this->type == 'single') {
            return __('admin.once');
        }

        if ($this->period == 1) {
            $period = __('admin.day');
        } elseif ($this->period == 7) {
            $period = __('admin.week');
        } elseif ($this->period == 30) {
            $period = __('admin.month');
        } elseif ($this->period == 90) {
            $period = __('admin.quarter');
        } elseif ($this->period == 180) {
            $period = __('admin.semi_year');
        } elseif ($this->period == 365) {
            $period = __('admin.year');
        } elseif ($this->period == 730) {
            $period = __('admin.per_years', ['years' => 2]);
        } elseif ($this->period == 1825) {
            $period = __('admin.per_years', ['years' => 5]);
        } elseif ($this->period == 3650) {
            $period = __('admin.per_years', ['years' => 10]);
        } else {
            $period = __('admin.day');
        }

        return $period;
    }

    public function periodToHuman(): string
    {
        if ($this->type == 'single') {
            return __('admin.just_once');
        }

        if ($this->period == 1) {
            $period = __('admin.daily');
        } elseif ($this->period == 7) {
            $period = __('admin.weekly');
        } elseif ($this->period == 30) {
            $period = __('admin.monthly');
        } elseif ($this->period == 90) {
            $period = __('admin.quarterly');
        } elseif ($this->period == 180) {
            $period = __('admin.semi_yearly');
        } elseif ($this->period == 365) {
            $period = __('admin.yearly');
        } elseif ($this->period == 730) {
            $period = __('admin.per_years', ['years' => 2]);
        } elseif ($this->period == 1825) {
            $period = __('admin.per_years', ['years' => 5]);
        } elseif ($this->period == 3650) {
            $period = __('admin.per_years', ['years' => 10]);
        } else {
            $period = __('admin.daily');
        }

        return $period;
    }
}
