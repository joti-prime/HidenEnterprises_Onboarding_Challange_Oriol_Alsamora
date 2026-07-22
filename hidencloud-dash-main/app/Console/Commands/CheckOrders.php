<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;

class CheckOrders extends Command
{
    protected $signature = 'orders:check';
    protected $description = 'Check and update orders statuses';

    public function handle(): void
    {
        $orders = Order::where('expires_at', '<', now())->get();
        $this->info("Found {$orders->count()} to check");
        $orders->each(function ($order) {
            $payments = Payment::where('type', '!=', 'subscription')->whereStatus('paid')->whereOrderId($order->id)->get();
            $payments->each(function ($payment) use ($order) {
                $service = new $payment->service_handler;
                $service->onCancel($payment);
                Order::find($order->id)->update(['status' => 'deactive', 'cancelled_at' => now()]);
                Payment::find($payment->id)->update(['status' => 'unpaid']);
                $this->warn("Order with ID: {$order->id} has been canceled due to non-payment");
            });
        });
    }
}
