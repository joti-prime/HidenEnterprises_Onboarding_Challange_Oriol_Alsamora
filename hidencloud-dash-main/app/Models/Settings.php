<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * App\Models\Settings
 *
 * @property int $id
 * @property string $name
 * @property string|null $type
 * @property string|null $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Settings newModelQuery()
 * @method static Builder|Settings newQuery()
 * @method static Builder|Settings query()
 * @method static Builder|Settings whereCreatedAt($value)
 * @method static Builder|Settings whereData($value)
 * @method static Builder|Settings whereId($value)
 * @method static Builder|Settings whereName($value)
 * @method static Builder|Settings whereType($value)
 * @method static Builder|Settings whereUpdatedAt($value)
 *
 * @property string $key
 * @property string|null $value
 *
 * @method static Builder|Settings whereKey($value)
 * @method static Builder|Settings whereValue($value)
 *
 * @mixin \Eloquent
 */
class Settings extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = ['key', 'value'];

    protected static $settings = null;

    public static function getAllSettings(): ?array
    {
        if (self::$settings === null) {
            try {
                self::$settings = Settings::query()->pluck('value', 'key')->all();
            } catch (\Illuminate\Database\QueryException|\Exception $e) {
                self::$settings = [];
            }
        }

        return self::$settings;
    }

    /**
     * @return mixed|null
     */
    public static function get(mixed $key, mixed $default = null): mixed
    {
        $settings = self::getAllSettings();

        // ensure that encrypted keys are decrypted
        // when they are returned
        if (Str::contains($key, 'encrypted')) {
            if (self::has($key)) {
                return decrypt($settings[$key]);
            }

            return $default;
        }

        return $settings[$key] ?? $default;
    }

    /**
     * @return mixed|null
     */
    public static function getJson($key, $property, $default = null): mixed
    {
        // Get the JSON setting
        $setting = self::get($key);

        // If the setting is null, return the default value
        if ($setting === null) {
            return $default;
        }

        // Decode the JSON setting into an array
        $json = json_decode($setting, true);

        // If the JSON decode failed, return the default value
        if ($json === null) {
            return $default;
        }

        // Return the requested property from the array, or the default value if it doesn't exist
        return $json[$property] ?? $default;
    }

    public static function store(Request $request): void
    {
        foreach ($request->except('_token') as $key => $value) {
            Settings::put($key, $value);
        }
    }

    public static function has($key): bool
    {
        return array_key_exists($key, self::getAllSettings());
    }

    public static function put(string $key, $value): void
    {
        // If the value is an array, convert it to JSON
        if (is_array($value)) {
            $value = json_encode($value);
        }

        // ensure that encrypted keys are
        // encrypted before being stored
        if (Str::contains($key, 'encrypted')) {
            $value = encrypt($value);
        }
        try {
            Settings::query()->updateOrInsert(
                ['key' => $key],
                ['value' => $value]
            );
        } catch (\Illuminate\Database\QueryException|\Exception $e) {

        }

    }

    public static function forget(string $key): void
    {
        Settings::query()->where('key', $key)->delete();
    }

    /**
     * Scope a query to only include models where 'key' column 'like' the specified value.
     */
    public function scopeLike(Builder $query, mixed $column, mixed $value): Builder
    {
        return $query->where($column, 'LIKE', '%' . $value . '%');
    }
}
