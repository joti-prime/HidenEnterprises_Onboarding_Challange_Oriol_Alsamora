<?php

namespace App\Traits\Models;

use App\Facades\ModelSettingsManager;
use App\Models\ModelSettings;

trait HasSettings
{
    protected $settingsManagerInstance;

    public function modelSettings()
    {
        return $this->morphMany(ModelSettings::class, 'metable');
    }

    public function settings(?string $key = null, $default = null)
    {
        if (!$this->settingsManagerInstance) {
            $this->settingsManagerInstance = new ModelSettingsManager($this);
        }

        if ($key) {
            return $this->settingsManagerInstance->get($key, $default);
        }

        return $this->settingsManagerInstance;
    }
}
