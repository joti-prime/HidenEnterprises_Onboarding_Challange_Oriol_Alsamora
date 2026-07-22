<?php

namespace App\Handlers;

use App\Facades\ServiceHandler;
use App\Models\Payment;
use App\Http\Controllers\Client\ConfigurableOptionUpgradeController;

class ConfigurableOptionUpgrade extends ServiceHandler
{
    /**
     * This event will be fired once the payment is completed
     */
    public function onPaymentCompleted(Payment $payment)
    {
        $order = $payment->order;
        $newOptions = $payment->options['new_options'];
        $currentOptions = $payment->options['current_options'];

        // Update price modifiers
        $controller = new ConfigurableOptionUpgradeController();
        $controller->updatePriceModifiers($order, $newOptions);
        
        // Track the change
        $this->trackConfigurableChange($order, $currentOptions, $newOptions, 'upgrade');

        // Apply changes to service (Pterodactyl server)
        try {
            $service = $order->service();
            if (method_exists($service, 'upgradeConfigurableOptions')) {
                $service->upgradeConfigurableOptions($order, $newOptions, $currentOptions);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the payment - user has already paid
            \App\Models\ErrorLog::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'source' => 'configurable_options_upgrade_service_error',
                'severity' => 'ERROR',
                'message' => 'Failed to apply configurable options upgrade to service: ' . $e->getMessage(),
            ]);
        }

        // Fire event
        event(new \App\Events\Order\ConfigurableOptionsUpgraded($order, $currentOptions, $newOptions, $payment));
        
        // Fire the standard upgrade webhook event
        $order->fireEvent('upgrade');
    }

    /**
     * This event will be fired once the payment fails
     */
    public function onPaymentFailed(Payment $payment)
    {
        // Nothing to do - no changes were applied
    }

    public function onPaymentPending(Payment $payment)
    {
        // Nothing to do - wait for completion
    }

    public function onPaymentDeclined(Payment $payment)
    {
        // Nothing to do - no changes were applied
    }

    public function onPaymentExpired(Payment $payment)
    {
        // Nothing to do - no changes were applied
    }

    /**
     * Track a configurable option change
     */
    private function trackConfigurableChange($order, array $currentOptions, array $newOptions, string $type = 'upgrade'): void
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
}