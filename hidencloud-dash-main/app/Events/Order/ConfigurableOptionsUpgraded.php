<?php

namespace App\Events\Order;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConfigurableOptionsUpgraded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $oldOptions;
    public $newOptions;
    public $payment;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     * @param array $oldOptions
     * @param array $newOptions
     * @param Payment $payment
     */
    public function __construct(Order $order, array $oldOptions, array $newOptions, Payment $payment)
    {
        $this->order = $order;
        $this->oldOptions = $oldOptions;
        $this->newOptions = $newOptions;
        $this->payment = $payment;
    }
}