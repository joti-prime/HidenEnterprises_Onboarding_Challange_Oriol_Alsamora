<?php

namespace App\Models;

use App\Models\OrderScope\OrderByScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Categories
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $name
 * @property string $link
 * @property string $icon
 * @property int $order
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Package> $packages
 * @property-read int|null $packages_count
 * @property-read \App\Models\Categories|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Categories> $children
 *
 * @mixin \Eloquent
 */
class Categories extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = ['parent_id', 'name', 'link', 'aliases', 'icon', 'order', 'status', 'description'];

    protected $casts = [
        'aliases' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new OrderByScope('order'));
    }

    public function icon()
    {
        return asset('storage/products/' . $this->icon);
    }

    public function packages()
    {
        return $this->hasMany(Package::class, 'category_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function isGroup(): bool
    {
        return $this->parent_id === null && $this->children()->exists();
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
