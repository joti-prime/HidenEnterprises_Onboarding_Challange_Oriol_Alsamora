<?php

namespace App\Console\Commands\Cronjobs\Orders;

use App\Models\ErrorLog;
use App\Models\Order;
use Illuminate\Console\Command;

class SuspendCancelledOrders extends Command
{
    protected $signature = 'cron:orders:suspend-cancelled';

    protected $description = 'Terminate orders that are cancelled and past grace period';

    public function handle()
    {
        $cancelled_orders = Order::whereStatus('cancelled')->where('cancelled_at', '<', now())->get();

        $this->info('Loaded a list of expired orders '. $cancelled_orders->count());
        $progressBar = $this->output->createProgressBar(count($cancelled_orders));
        $progressBar->start();

        foreach ($cancelled_orders as $order) {

            // make sure one time orders are skipped
            if (!$order->isRecurring()) {
                continue;
            }

            try {

                $order->due_date = now();
                $order->save();

                // terminate the order directly instead of suspending
                // since it was cancelled voluntarily by the customer
                $order->terminate();
            } catch (\Exception $error) {
                // Catch any exceptions thrown by the service handler
                // Handle the error appropriately and register it as an event
                ErrorLog::updateOrCreate([
                    'source' => 'cron:orders:terminate-cancelled',
                    'severity' => 'CRITICAL',
                    'message' => "Automatic termination service was unable to terminate cancelled order $order->id - Error: ". $error->getMessage(),
                ]);

                $order->forceTerminate();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line(''); // Move to the next line after progress bar completion

        $this->info('Task Completed: all cancelled orders were terminated');

    }
}
