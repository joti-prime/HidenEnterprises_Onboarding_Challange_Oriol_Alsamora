<?php

namespace App\Facades;

use Illuminate\Database\Eloquent\Model;

class ModelSettingsManager
{
    protected $model;

    protected $settingsCache = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->loadSettings();
    }

    protected function loadSettings()
    {
        $this->settingsCache = $this->model->modelSettings()->pluck('value', 'key')->toArray();
    }

    public function get(string $key, $default = null)
    {
        if (strpos($key, 'encrypted') !== false) {
            return isset($this->settingsCache[$key]) ? decrypt($this->settingsCache[$key]) : $default;
        }

        return $this->settingsCache[$key] ?? $default;
    }

    public function put(string $key, $value)
    {
        // if the key contains "encrypted" word, encrypt the value
        if (strpos($key, 'encrypted') !== false) {
            $value = encrypt($value);
        }

        if (isset($this->settingsCache[$key]) && $this->settingsCache[$key] !== $value) {
            $this->model->modelSettings()->where('key', $key)->update(['value' => $value]);
        } else {
            $this->model->modelSettings()->create([
                'key' => $key,
                'value' => $value,
            ]);
        }

        $this->settingsCache[$key] = $value;
    }

    public function store(array $settings)
    {
        foreach ($settings as $key => $value) {
            $this->put($key, $value);
        }
    }

    public function delete(string $key): bool
    {
        $deleted = $this->model->modelSettings()->where('key', $key)->delete();

        if ($deleted) {
            unset($this->settingsCache[$key]);
        }

        return $deleted;
    }

    public function all(): array
    {
        return $this->settingsCache;
    }
}
