<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderPriceModifier;
use App\Rules\ConfigurableOptionLimits;
use App\Handlers\ConfigurableOptionUpgrade;
use App\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ConfigurableOptionUpgradeController extends Controller
{
    /**
     * Show the configurable options upgrade form
     */
    public function show(Order $order)
    {
        if (!$this->canUpgradeOptions($order)) {
            return redirect()->back()->withError(__('This service does not support configurable option upgrades'));
        }

        try {
            $packageOptions = ConfigurableOptionLimits::getPackageOptions($order->package_id);
            $currentOptions = $this->getCurrentOptions($order);
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        }

        // Get remaining daily changes
        $remainingChanges = $this->getRemainingChanges($order);

        return Theme::view('orders.upgrade-options', compact('order', 'packageOptions', 'currentOptions', 'remainingChanges'));
    }

    /**
     * Process the configurable options upgrade
     */
    public function upgrade(Request $request, Order $order)
    {
        if (!$this->canUpgradeOptions($order)) {
            return redirect()->back()->withError(__('This service does not support configurable option upgrades'));
        }

        // Check daily limit for configurable option changes
        if (!$this->checkDailyLimit($order)) {
            return redirect()->back()->withError(__('You have reached the daily limit of 2 configurable option changes. Please try again tomorrow.'));
        }

        $packageOptions = ConfigurableOptionLimits::getPackageOptions($order->package_id);
        $currentOptions = $this->getCurrentOptions($order);

        // Build validation rules
        $rules = [];
        foreach ($packageOptions as $optionKey => $limits) {
            $rules[$optionKey] = [
                'required',
                'integer',
                'min:' . $limits['min'],
                'max:' . $limits['max'],
                new ConfigurableOptionLimits($order->package_id, $optionKey, $currentOptions[$optionKey] ?? $limits['base'])
            ];
        }
        
        // Check if it's a downgrade (no gateway required)
        $tempValidated = $request->validate($rules);
        $tempPriceDifference = $this->calculatePriceDifference($order->package_id, $currentOptions, $tempValidated);
        
        // Only require gateway for upgrades (positive price difference)
        if ($tempPriceDifference > 0) {
            $rules['gateway'] = 'required';
        }

        $validated = $request->validate($rules);
        
        // Additional backend validation to prevent exploits
        foreach ($validated as $optionKey => $newValue) {
            if ($optionKey === 'gateway') continue;
            
            $limits = $packageOptions[$optionKey];
            $currentValue = $currentOptions[$optionKey] ?? $limits['base'];
            
            // Validate the new value is within allowed bounds
            if ($newValue < $limits['min'] || $newValue > $limits['max']) {
                return redirect()->back()->withError(__('Invalid value for :option. Must be between :min and :max.', [
                    'option' => ucfirst(str_replace('_', ' ', $optionKey)),
                    'min' => $limits['min'],
                    'max' => $limits['max']
                ]));
            }
            
            // Validate the value follows the step increment
            if (($newValue - $limits['base']) % $limits['step'] !== 0) {
                return redirect()->back()->withError(__('Invalid value for :option. Must be incremented by :step from base value :base.', [
                    'option' => ucfirst(str_replace('_', ' ', $optionKey)),
                    'step' => $limits['step'],
                    'base' => $limits['base']
                ]));
            }
        }

        // Calculate price differences
        $priceDifference = $this->calculatePriceDifference($order->package_id, $currentOptions, $validated);
        
        // Additional validation: ensure price calculation matches expected values
        $serverSidePriceDifference = $this->calculatePriceDifference($order->package_id, $currentOptions, $validated);
        if (abs($priceDifference - $serverSidePriceDifference) > 0.01) {
            return redirect()->back()->withError(__('Price calculation mismatch detected. Please refresh the page and try again.'));
        }
        
        // For downgrades, apply immediately without payment
        if ($priceDifference <= 0) {
            // Track the change before applying
            $this->trackConfigurableChange($order, $currentOptions, $validated, 'downgrade');
            
            return $this->applyDowngrade($order, $validated, $currentOptions);
        }

        // For upgrades, check if upgrade fee should be applied
        $upgradeFee = 0;
        if (isset($validated['gateway'])) {
            // Get the selected gateway
            $gateway = \App\Models\Gateways\Gateway::find($validated['gateway']);
            
            // Validate gateway exists
            if (!$gateway) {
                return redirect()->back()->withError(__('Invalid payment method selected.'));
            }
            
            // Apply 1.5€ upgrade fee if payment method is not Balance
            // This is calculated server-side to prevent frontend manipulation
            if ($gateway->name !== 'Balance') {
                $upgradeFee = 1.5;
            }
        }
        
        // Calculate total amount (price difference + upgrade fee)
        // Round to 2 decimal places to avoid floating point issues
        $totalAmount = round($priceDifference + $upgradeFee, 2);

        // For upgrades, create payment
        $upgradeDescription = "Upgrade Configurable Options - " . $order->name;
        if ($upgradeFee > 0) {
            $upgradeDescription .= " (includes €1.50 processing fee)";
        }

        $payment = Payment::generate([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'description' => $upgradeDescription,
            'amount' => number_format($totalAmount, 2),
            'options' => array_merge($validated, [
                'type' => 'configurable_options_upgrade',
                'current_options' => $currentOptions,
                'new_options' => $validated,
                'upgrade_fee' => $upgradeFee,
                'base_amount' => $priceDifference
            ]),
            'handler' => ConfigurableOptionUpgrade::class,
            'show_as_unpaid_invoice' => false,
        ]);

        return redirect()->route('invoice.pay', ['payment' => $payment->id, 'gateway' => $request->input('gateway')]);
    }

    /**
     * Check if the order can upgrade configurable options
     */
    private function canUpgradeOptions(Order $order): bool
    {
        // Check if order is active
        if ($order->status !== 'active') {
            return false;
        }

        // Check if service supports upgrades
        if (!$order->getService()->canUpgrade()) {
            return false;
        }

        // Check if package allows upgrading
        if (!$order->package->settings('allow_upgrading', true)) {
            return false;
        }

        // Check if it's a Pterodactyl service (only supported service for now)
        if (strtolower($order->package->service) !== 'pterodactyl') {
            return false;
        }

        // Check if package has configurable options defined
        $packageOptions = ConfigurableOptionLimits::getPackageOptions($order->package_id);
        if (empty($packageOptions)) {
            return false;
        }

        return true;
    }

    /**
     * Get current configurable options for the order
     */
    private function getCurrentOptions(Order $order): array
    {
        $currentOptions = [];
        $packageOptions = ConfigurableOptionLimits::getPackageOptions($order->package_id);
        
        // Define the keys we're looking for
        $targetKeys = ['cpu_limit', 'memory_limit', 'disk_limit', 'backup_limit', 'allocation_limit', 'database_limit'];

        // Get all price modifiers for this order
        $allModifiers = OrderPriceModifier::where('order_id', $order->id)->get();
        
        // Filter and validate modifiers
        $validModifiers = [];
        $duplicateKeys = [];
        
        foreach ($allModifiers as $modifier) {
            $key = $modifier->key;
            
            // Only process our target keys
            if (in_array($key, $targetKeys)) {
                if (isset($validModifiers[$key])) {
                    // Found duplicate key
                    $duplicateKeys[] = $key;
                } else {
                    $validModifiers[$key] = $modifier;
                }
            }
        }
        
        // If there are duplicates, throw an error
        if (!empty($duplicateKeys)) {
            throw new \Exception('Duplicate configuration options found: ' . implode(', ', $duplicateKeys) . '. Cannot perform automatic upgrade. Please contact administrator.');
        }

        foreach ($packageOptions as $optionKey => $limits) {
            if (isset($validModifiers[$optionKey])) {
                $value = (int) $validModifiers[$optionKey]->value;
                
                // Convert values to match our expected format
                switch ($optionKey) {
                    case 'cpu_limit':
                        // CPU is stored as percentage (100% = 1 core)
                        $currentOptions[$optionKey] = max(1, $value / 100);
                        break;
                    case 'backup_limit':
                        // Backup is stored in MB, convert to GB
                        $currentOptions[$optionKey] = $value / 1024;
                        break;
                    default:
                        $currentOptions[$optionKey] = $value;
                        break;
                }
            } else {
                // Use base value if no modifier exists
                $currentOptions[$optionKey] = $limits['base'];
            }
        }

        return $currentOptions;
    }

    /**
     * Initialize configurable options for existing orders
     */
    public static function initializeOptionsForOrder(Order $order): void
    {
        $packageOptions = ConfigurableOptionLimits::getPackageOptions($order->package_id);
        
        if (empty($packageOptions)) {
            return;
        }

        foreach ($packageOptions as $optionKey => $limits) {
            // Check if option already exists
            $existingModifier = OrderPriceModifier::where('order_id', $order->id)
                ->where('type', 'configurable_option')
                ->where('key', $optionKey)
                ->first();

            if (!$existingModifier) {
                // Create base option
                OrderPriceModifier::create([
                    'order_id' => $order->id,
                    'type' => 'configurable_option',
                    'key' => $optionKey,
                    'value' => $limits['base'],
                    'price' => 0, // Base is included in package price
                    'description' => ucfirst(str_replace('_', ' ', $optionKey)),
                ]);
            }
        }
    }

    /**
     * Calculate price difference between current and new options (with proration)
     */
    private function calculatePriceDifference(int $packageId, array $currentOptions, array $newOptions): float
    {
        $currentTotal = 0;
        $newTotal = 0;
        $packageOptions = ConfigurableOptionLimits::getPackageOptions($packageId);

        foreach ($newOptions as $optionKey => $newValue) {
            if ($optionKey === 'gateway') continue;

            $currentValue = $currentOptions[$optionKey] ?? $packageOptions[$optionKey]['base'];
            $limits = $packageOptions[$optionKey];
            
            // Calculate monthly prices first
            $currentUnitsAboveBase = ($currentValue - $limits['base']) / $limits['step'];
            $newUnitsAboveBase = ($newValue - $limits['base']) / $limits['step'];
            
            $currentMonthlyPrice = $currentUnitsAboveBase * $limits['price_per_unit'];
            $newMonthlyPrice = $newUnitsAboveBase * $limits['price_per_unit'];
            
            $currentTotal += $currentMonthlyPrice;
            $newTotal += $newMonthlyPrice;
        }

        $monthlyDifference = $newTotal - $currentTotal;
        
        // Apply proration for upgrades using correct formula: (monthlyPrice / 30 * daysRemaining)
        if ($monthlyDifference > 0) {
            $order = request()->route('order');
            $daysRemaining = now()->diffInDays($order->due_date);
            
            return ($monthlyDifference / 30 * $daysRemaining);
        }
        
        // For downgrades, convert monthly to period difference
        $order = request()->route('order');
        $orderPeriod = $order->price()->period;
        return ($monthlyDifference / 30) * $orderPeriod;
    }

    /**
     * Apply downgrade immediately
     */
    private function applyDowngrade(Order $order, array $newOptions, array $currentOptions)
    {
        // Update price modifiers
        $this->updatePriceModifiers($order, $newOptions);

        // Apply changes to Pterodactyl immediately
        try {
            $service = $order->service();
            if (method_exists($service, 'upgradeConfigurableOptions')) {
                $service->upgradeConfigurableOptions($order, $newOptions, $currentOptions);
            }
        } catch (\Exception $e) {
            // Revert price modifiers if service update fails
            $this->updatePriceModifiers($order, $currentOptions);
            return redirect()->back()->withError('Failed to apply changes to server: ' . $e->getMessage());
        }

        // Fire event
        event(new \App\Events\Order\ConfigurableOptionsDowngraded($order, $currentOptions, $newOptions));
        
        // Fire the standard upgrade webhook event (even for downgrades, as it's still a change)
        $order->fireEvent('upgrade');

        return redirect()->route('dashboard')->with('success', __('Success! Configurable options have been updated successfully'));
    }

    /**
     * Update price modifiers for the order
     */
    public function updatePriceModifiers(Order $order, array $newOptions)
    {
        $packageOptions = ConfigurableOptionLimits::getPackageOptions($order->package_id);

        foreach ($newOptions as $optionKey => $newValue) {
            if ($optionKey === 'gateway') continue;

            $limits = $packageOptions[$optionKey];
            
            // Convert values back to storage format
            $storageValue = $newValue;
            $dailyPrice = 0;
            
            switch ($optionKey) {
                case 'cpu_limit':
                    // Convert cores to percentage for storage
                    $storageValue = $newValue * 100;
                    $unitsAboveBase = ($newValue - $limits['base']) / $limits['step'];
                    $dailyPrice = ($unitsAboveBase * $limits['price_per_unit']) / 30;
                    break;
                case 'backup_limit':
                    // Convert GB to MB for storage
                    $storageValue = $newValue * 1024;
                    $unitsAboveBase = ($newValue - $limits['base']) / $limits['step'];
                    $dailyPrice = ($unitsAboveBase * $limits['price_per_unit']) / 30;
                    break;
                default:
                    $unitsAboveBase = ($newValue - $limits['base']) / $limits['step'];
                    $dailyPrice = ($unitsAboveBase * $limits['price_per_unit']) / 30;
                    break;
            }

            // Find existing price modifier
            $existingModifier = OrderPriceModifier::where('order_id', $order->id)
                ->where('key', $optionKey)
                ->first();

            if ($existingModifier) {
                // Update existing modifier
                $existingModifier->update([
                    'value' => $storageValue,
                    'daily_price' => $dailyPrice,
                ]);
            } else {
                // Create new modifier with proper description
                $descriptions = [
                    'cpu_limit' => 'CPU Cores - COBALT 100 [Arm64]',
                    'memory_limit' => 'RAM (DDR5)',
                    'disk_limit' => 'Storage Space (NVMe)',
                    'backup_limit' => 'Backup Space',
                    'allocation_limit' => 'Ports / Allocations',
                    'database_limit' => 'Databases',
                ];

                OrderPriceModifier::create([
                    'order_id' => $order->id,
                    'description' => $descriptions[$optionKey] ?? ucfirst(str_replace('_', ' ', $optionKey)),
                    'type' => 'custom_option',
                    'key' => $optionKey,
                    'value' => $storageValue,
                    'base_price' => 0,
                    'daily_price' => $dailyPrice,
                    'cancellation_fee' => 0,
                    'upgrade_fee' => 0,
                    'is_active' => 1,
                ]);
            }
        }
    }

    /**
     * Check if the order has reached the daily limit for configurable option changes
     */
    private function checkDailyLimit(Order $order): bool
    {
        $dailyLimit = 2; // Maximum 2 changes per day
        $today = now()->startOfDay();
        
        // Count changes today using ErrorLog
        $changesCount = \App\Models\ErrorLog::where('order_id', $order->id)
            ->where('source', 'configurable_options_change')
            ->where('severity', 'INFO')
            ->where('created_at', '>=', $today)
            ->count();
            
        return $changesCount < $dailyLimit;
    }

    /**
     * Track a configurable option change
     */
    private function trackConfigurableChange(Order $order, array $currentOptions, array $newOptions, string $type = 'upgrade'): void
    {
        $changes = [];
        foreach ($newOptions as $key => $newValue) {
            if ($key === 'gateway') continue;
            
            $currentValue = $currentOptions[$key] ?? 0;
            if ($newValue != $currentValue) {
                $changes[] = "{$key}: {$currentValue} → {$newValue}";
            }
        }
        
        $message = "Configurable options {$type} - Order #{$order->id} - User #{$order->user_id} - Changes: " . implode(', ', $changes);
        
        \App\Models\ErrorLog::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'source' => 'configurable_options_change',
            'severity' => 'INFO',
            'message' => $message,
        ]);
    }

    /**
     * Get remaining daily changes for display
     */
    public function getRemainingChanges(Order $order): int
    {
        $dailyLimit = 2;
        $today = now()->startOfDay();
        
        $changesCount = \App\Models\ErrorLog::where('order_id', $order->id)
            ->where('source', 'configurable_options_change')
            ->where('severity', 'INFO')
            ->where('created_at', '>=', $today)
            ->count();
            
        return max(0, $dailyLimit - $changesCount);
    }
}