<?php

namespace App\Console\Commands\Cronjobs\Orders;

use App\Models\ErrorLog;
use App\Models\Order;
use Illuminate\Console\Command;

class DeleteTerminateOrders extends Command
{
    protected $signature = 'cron:orders:delete-terminate';

    protected $description = 'Delete terminate orders';

    public function handle(): void
    {
        if (!settings('orders::delete_terminated', false)) {
            $this->info('Task Completed: terminate order deletion is disabled');

            return;
        }

        $terminated_orders = Order::whereStatus('terminated')->get();
        $this->info('Loaded a list of terminated orders '. $terminated_orders->count());
        $progressBar = $this->output->createProgressBar(count($terminated_orders));
        $progressBar->start();

        foreach ($terminated_orders as $order) {
            try {
                $order->delete();
            } catch (\Exception $error) {
                ErrorLog::updateOrCreate([
                    'source' => 'cron:orders:delete-terminate',
                    'severity' => 'CRITICAL',
                    'message' => "Automatic delete terminate order $order->id - Error: ". $error->getMessage(),
                ]);
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');
        $this->info('Task Completed: all terminated orders deleted');
    }
}
