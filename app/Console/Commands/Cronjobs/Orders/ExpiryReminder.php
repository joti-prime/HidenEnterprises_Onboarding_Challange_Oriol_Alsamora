<?php

namespace App\Console\Commands\Cronjobs\Orders;

use App\Models\Order;
use Illuminate\Console\Command;

class ExpiryReminder extends Command
{
    protected $signature = 'cron:orders:remind-expiry';

    protected $description = 'Remind users of upcoming order expiry';

    public function handle()
    {
        if (!settings('email:renewal_reminder', true)) {
            $this->info('Renewal reminder emails are disabled');

            return;
        }

        // define the reminder frequency
        $reminderFrequency = settings('first_renewal_reminder_frequency', 3);

        // get orders that are 3 days away from expiry
        $orders = Order::where('due_date', '<=', now()->addDays($reminderFrequency))->where('status', 'active')->get();

        $this->info('Loaded a list of active orders '. $orders->count());

        foreach ($orders as $order) {
            if (!$order->isRecurring()) {
                continue;
            }

            // check if the last reminder was sent
            if ($order->settings()->get('first_reminder_at')) {
                if ($order->settings()->get('first_reminder_at') >= now()->subDays($reminderFrequency + 2)->timestamp) {
                    $this->info("Skipping order {$order->id} as the last reminder was sent recently");

                    continue;
                }
            }

            $order->user->email([
                'subject' => __('client.upcoming_invoice_subject', ['order' => $order->name]),
                'content' => __('client.upcoming_invoice_content', [
                    'order' => $order->name,
                    'due_date' => $order->due_date->translatedFormat(settings('date_format', 'd M Y')),
                ]),
                'button' => [
                    'name' => __('client.pay_now'),
                    'url' => route('service', ['order' => $order->id, 'page' => 'manage']),
                ],
            ]);

            $order->settings()->put('first_reminder_at', now()->timestamp);
        }
    }
}
