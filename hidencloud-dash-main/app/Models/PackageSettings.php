<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageSettings extends Model
{
    use HasFactory;

    protected $table = 'package_settings';

    protected $fillable = [
        'id',
        'package_id',
        'key',
        'value',
        'created_at',
        'updated_at',
    ];

    public static function getAllSettings($package_id): array
    {
        $settings = PackageSettings::query()->where('package_id', $package_id)->pluck('value', 'key')->all();

        return $settings ?? [];
    }

    public static function storeSettings($package_id, array $settings): void
    {
        foreach ($settings as $key => $value) {
            PackageSettings::put($package_id, $key, $value);
        }
    }

    public static function put($package_id, $key, $value): void
    {
        PackageSettings::updateOrCreate(
            ['package_id' => $package_id, 'key' => $key],
            ['value' => $value]
        );
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
