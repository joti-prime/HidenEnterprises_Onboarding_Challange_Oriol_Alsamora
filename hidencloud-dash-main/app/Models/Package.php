<?php

namespace App\Models;

use App\Events;
use App\Facades\Service;
use App\Models\OrderScope\OrderByScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Package
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string|null $description
 * @property string $icon
 * @property string $type
 * @property string|null $service
 * @property string $status
 * @property string $global_quantity
 * @property string $client_quantity
 * @property int $require_domain
 * @property array|null $data
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Categories|null $category
 * @property-read Collection<int, PackagePrice> $prices
 * @property-read int|null $prices_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Package newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Package newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Package onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Package query()
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereClientQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereGlobalQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereRequireDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereService($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Package withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Package withoutTrashed()
 *
 * @property string $setup_on
 * @property int|mixed $period
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Package whereSetupOn($value)
 *
 * @mixin \Eloquent
 */
class Package extends Model
{
    use HasFactory;

    protected $table = 'packages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Define dispatchable events
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => Events\Package\PackageCreated::class,
        'updated' => Events\Package\PackageUpdated::class,
        'deleted' => Events\Package\PackageDeleted::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new OrderByScope('order'));
    }

    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    public function prices()
    {
        return $this->hasMany(PackagePrice::class);
    }

    public function features()
    {
        return $this->hasMany(PackageFeature::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'package_id', 'id');
    }

    public function emails()
    {
        return $this->hasMany(PackageEmail::class);
    }

    public function webhooks()
    {
        return $this->hasMany(PackageWebhook::class);
    }

    public function configOptions()
    {
        return $this->hasMany(PackageConfigOption::class);
    }

    public function service()
    {
        return Service::find($this->service);
    }

    public function settings($key, $default = null)
    {
        $settings = PackageSettings::getAllSettings($this->id);

        return $settings[$key] ?? $default;
    }

    public function getAllPrices()
    {
        return $this->prices()->get();
    }

    public function getPriceById($price_id)
    {
        return $this->prices()->find($price_id)->price;
    }

    public function icon()
    {
        return asset('storage/products/' . $this->icon);
    }

    public function data($key, $default = '')
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    public function inStock(): bool
    {
        // check whether the global stock has reached 0
        if ($this->global_quantity == 0) {
            return false;
        }

        if ($this->client_quantity !== -1) {
            if (auth()->user()->orders()->where('package_id', $this->id)->count() >= $this->client_quantity) {
                return false;
            }
        }

        return true;
    }

    public function changeOrder($direction = 'up')
    {
        $allItems = self::orderBy('order', 'asc')->get();
        foreach ($allItems as $index => $item) {
            $item->order = $index + 1;
            $item->save();
        }
        $currentOrder = $this->order;
        if ($direction == 'up' && $currentOrder > 1) {
            $prevItem = self::where('order', '<', $currentOrder)->orderBy('order', 'desc')->first();
            if ($prevItem) {
                $this->order = $prevItem->order;
                $prevItem->order = $currentOrder;
                $prevItem->save();
            }
        } elseif ($direction == 'down') {
            $nextItem = self::where('order', '>', $currentOrder)->orderBy('order', 'asc')->first();
            if ($nextItem) {
                $this->order = $nextItem->order;
                $nextItem->order = $currentOrder;
                $nextItem->save();
            }
        }

        return $this->save();
    }
}
