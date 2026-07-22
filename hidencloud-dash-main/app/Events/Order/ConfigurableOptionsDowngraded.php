<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConfigurableOptionsDowngraded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $oldOptions;
    public $newOptions;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     * @param array $oldOptions
     * @param array $newOptions
     */
    public function __construct(Order $order, array $oldOptions, array $newOptions)
    {
        $this->order = $order;
        $this->oldOptions = $oldOptions;
        $this->newOptions = $newOptions;
    }
}