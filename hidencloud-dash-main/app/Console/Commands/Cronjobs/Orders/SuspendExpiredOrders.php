<?php

namespace App\Console\Commands\Cronjobs\Orders;

use App\Models\ErrorLog;
use App\Models\Order;
use Illuminate\Console\Command;

class SuspendExpiredOrders extends Command
{
    protected $signature = 'cron:orders:suspend-expired';

    protected $description = 'Suspend orders that are expired';

    public function handle()
    {
        $expired_orders = Order::getExpiredOrders();

        $this->info('Loaded a list of expired orders '. $expired_orders->count());
        $progressBar = $this->output->createProgressBar(count($expired_orders));
        $progressBar->start();

        foreach ($expired_orders as $order) {

            // make sure one time orders are skipped
            if (!$order->isRecurring() or $order->isSubscription()) {
                $this->newLine();
                $this->warn("Order $order->id is not a recurring order or is a subscription, skipping...");
                continue;
            }

            try {
                // email
                app()->setLocale($order->user->language);
                $order->user->email([
                    'subject' => __('admin.email_suspended_subject'),
                    'content' => emailMessage('suspended', $order->user->language) . __('admin.email_suspended_content', [
                        'period' => $order->period(),
                        'due_date' => $order->due_date->translatedFormat(settings('date_format', 'd M Y')),
                        'amount_rounded' => price($order->price['renewal_price']),
                        'order_id' => $order->id,
                        'order_name' => $order->name,
                    ]),
                    'button' => [
                        'name' => __('admin.email_suspended_button'),
                        'url' => route('dashboard'),
                    ],
                ]);

                $order->suspend();

            } catch (\Exception $error) {
                // Catch any exceptions thrown by the service handler
                // Handle the error appropriately and register it as an event
                ErrorLog::updateOrCreate([
                    'source' => 'cron:orders:suspend-expired',
                    'severity' => 'CRITICAL',
                    'message' => "Automatic suspension service was unable to suspend order $order->id - Error: ". $error->getMessage(),
                ]);

                $order->forceSuspend();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line(''); // Move to the next line after progress bar completion

        $this->info('Task Completed: all expired orders were suspended');

    }
}
