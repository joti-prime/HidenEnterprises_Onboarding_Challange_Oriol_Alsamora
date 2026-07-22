<?php

namespace App\Console\Commands\Cronjobs\Orders;

use App\Models\ErrorLog;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TerminateSuspendedOrders extends Command
{
    protected $signature = 'cron:orders:terminate-suspended';

    protected $description = 'Terminate suspended orders';

    public function handle()
    {
        $terminated_orders = Order::whereStatus('suspended')->whereNotNull('due_date')->where('due_date', '<', Carbon::now()->subDays(settings('orders::terminate_suspended_after', 7)))->get();

        $this->info('Loaded a list of expired orders '. $terminated_orders->count());
        $progressBar = $this->output->createProgressBar(count($terminated_orders));
        $progressBar->start();

        foreach ($terminated_orders as $order) {

            // make sure one time orders are skipped
            if (!$order->isRecurring()) {
                continue;
            }

            try {
                // email
                app()->setLocale($order->user->language);
                $order->user->email([
                    'subject' => __('admin.email_terminated_subject'),
                    'content' => emailMessage('terminated', $order->user->language) . __('admin.email_terminated_content', [
                        'order_id' => $order->id,
                        'due_date' => $order->due_date->translatedFormat(settings('date_format', 'd M Y')),
                        'period' => $order->period(),
                        'amount' => price($order->price['renewal_price']),
                        'order_name' => $order->name,
                    ]),
                ]);

                $order->terminate();

            } catch (\Exception $error) {
                // Catch any exceptions thrown by the service handler
                // Handle the error appropriately and register it as an event
                ErrorLog::updateOrCreate([
                    'source' => 'cron:orders:suspend-expired',
                    'severity' => 'CRITICAL',
                    'message' => "Automatic termination service was unable to terminate order $order->id - Error: ". $error->getMessage(),
                ]);

                $order->forceTerminate();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line(''); // Move to the next line after progress bar completion

        $this->info('Task Completed: all suspended orders above the limit were terminated');

    }
}
