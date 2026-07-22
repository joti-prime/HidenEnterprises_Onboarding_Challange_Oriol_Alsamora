<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ConfigurableOptionLimits implements Rule
{
    protected $packageId;
    protected $optionKey;
    protected $currentValue;
    
    // Define package limits
    protected $packageLimits = [
        // Server Lite (ARM Cobalt 100) - Package ID 345
        345 => [
            'cpu_limit' => ['min' => 1, 'max' => 4, 'step' => 1, 'base' => 1, 'price_per_unit' => 0.50],
            'memory_limit' => ['min' => 1024, 'max' => 6144, 'step' => 1024, 'base' => 1024, 'price_per_unit' => 0.50], // MB
            'disk_limit' => ['min' => 10240, 'max' => 51200, 'step' => 5120, 'base' => 10240, 'price_per_unit' => 0.25], // MB
            'backup_limit' => ['min' => 10, 'max' => 50, 'step' => 5, 'base' => 10, 'price_per_unit' => 0.25], // GB
            'allocation_limit' => ['min' => 1, 'max' => 2, 'step' => 1, 'base' => 1, 'price_per_unit' => 0.50],
            'database_limit' => ['min' => 1, 'max' => 2, 'step' => 1, 'base' => 1, 'price_per_unit' => 0.50],
        ],
        // Server Standard (x64 EPYC 9004) - Package ID 344
        344 => [
            'cpu_limit' => ['min' => 2, 'max' => 6, 'step' => 1, 'base' => 2, 'price_per_unit' => 1.00],
            'memory_limit' => ['min' => 4096, 'max' => 16384, 'step' => 1024, 'base' => 4096, 'price_per_unit' => 1.00], // MB
            'disk_limit' => ['min' => 30720, 'max' => 102400, 'step' => 10240, 'base' => 30720, 'price_per_unit' => 0.50], // MB
            'backup_limit' => ['min' => 30, 'max' => 100, 'step' => 10, 'base' => 30, 'price_per_unit' => 0.50], // GB
            'allocation_limit' => ['min' => 2, 'max' => 5, 'step' => 1, 'base' => 2, 'price_per_unit' => 0.50],
            'database_limit' => ['min' => 2, 'max' => 5, 'step' => 1, 'base' => 2, 'price_per_unit' => 0.50],
        ],
        // Server Performance (x64 EPYC 9004/XEON E-2288G) - Package ID 346
        346 => [
            'cpu_limit' => ['min' => 3, 'max' => 10, 'step' => 1, 'base' => 3, 'price_per_unit' => 1.50],
            'memory_limit' => ['min' => 6144, 'max' => 32768, 'step' => 1024, 'base' => 6144, 'price_per_unit' => 2.00], // MB
            'disk_limit' => ['min' => 51200, 'max' => 204800, 'step' => 10240, 'base' => 51200, 'price_per_unit' => 0.50], // MB
            'backup_limit' => ['min' => 50, 'max' => 200, 'step' => 10, 'base' => 50, 'price_per_unit' => 0.50], // GB
            'allocation_limit' => ['min' => 3, 'max' => 10, 'step' => 1, 'base' => 3, 'price_per_unit' => 0.50],
            'database_limit' => ['min' => 3, 'max' => 10, 'step' => 1, 'base' => 3, 'price_per_unit' => 0.50],
        ],
    ];

    public function __construct($packageId, $optionKey, $currentValue = null)
    {
        $this->packageId = $packageId;
        $this->optionKey = $optionKey;
        $this->currentValue = $currentValue;
    }

    public function passes($attribute, $value)
    {
        if (!isset($this->packageLimits[$this->packageId])) {
            return false;
        }

        if (!isset($this->packageLimits[$this->packageId][$this->optionKey])) {
            return false;
        }

        $limits = $this->packageLimits[$this->packageId][$this->optionKey];

        // Check min and max
        if ($value < $limits['min'] || $value > $limits['max']) {
            return false;
        }

        // Check step validation (value must be reachable from base using step)
        if (($value - $limits['base']) % $limits['step'] !== 0) {
            return false;
        }

        // For downgrades, allow any value less than current
        if ($this->currentValue !== null && $value < $this->currentValue) {
            return true;
        }

        return true;
    }

    public function message()
    {
        if (!isset($this->packageLimits[$this->packageId][$this->optionKey])) {
            return 'Invalid option for this package.';
        }

        $limits = $this->packageLimits[$this->packageId][$this->optionKey];
        
        return "The :attribute must be between {$limits['min']} and {$limits['max']}, in increments of {$limits['step']}.";
    }

    /**
     * Get the limits for a specific package and option
     */
    public static function getLimits($packageId, $optionKey)
    {
        $instance = new self($packageId, $optionKey);
        return $instance->packageLimits[$packageId][$optionKey] ?? null;
    }

    /**
     * Calculate the price for an option value
     */
    public static function calculatePrice($packageId, $optionKey, $value)
    {
        $limits = self::getLimits($packageId, $optionKey);
        if (!$limits) {
            return 0;
        }

        $unitsAboveBase = ($value - $limits['base']) / $limits['step'];
        return $unitsAboveBase * $limits['price_per_unit'];
    }

    /**
     * Get all available options for a package
     */
    public static function getPackageOptions($packageId)
    {
        $instance = new self($packageId, '');
        return $instance->packageLimits[$packageId] ?? [];
    }
}