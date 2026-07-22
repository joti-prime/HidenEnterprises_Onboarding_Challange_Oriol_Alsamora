<?php

namespace App\Console\Commands\Cronjobs\Orders;

use App\Models\Order;
use App\Models\EmailHistory;
use Illuminate\Console\Command;

class BalanceAutoRenew extends Command
{
    protected $signature = 'cron:orders:balance-auto-renew';

    protected $description = 'Auto renew orders that have balance auto-renew enabled';

    public function handle()
    {
        // Get all active orders that are 3 days away from expiry
        $orders = Order::where('due_date', '<=', now()->addDays(5))->where('status', 'active')->where('auto_balance_renew', true)->get();

        $this->info('Loaded a list of active orders '. $orders->count());

        foreach ($orders as $order) {
            $user = $order->user;

            // check if order is recurring
            if (!$order->isRecurring()) {
                $order->auto_balance_renew = false;
                $order->save();

                continue;
            }

            // check if the user has enough balance
            if ($user->balance < $order->price()->renewal_price) {
                $this->notEnoughBalance($order);

                continue;
            }

            $user->balance(
                "Automatic renewal of order {$order->name} (#{$order->id})",
                '-',
                $order->price()->renewal_price
            );

            $order->extend($order->price()->period);

            $this->orderRenewed($order);
        }
    }

    protected function notEnoughBalance(Order $order)
    {
        // Check if we sent a notification for this order in the last 72 hours
        $recentNotification = EmailHistory::where('user_id', $order->user->id)
            ->where('identifier', 'like', 'insufficient_balance_order_' . $order->id . '_%')
            ->where('created_at', '>', now()->subHours(72))
            ->first();
        
        if ($recentNotification) {
            $this->info("Skipping notification for order #{$order->id} - already sent within 72 hours");
            return;
        }
        
        // Create a unique identifier for this notification
        $identifier = 'insufficient_balance_order_' . $order->id . '_' . now()->timestamp;
        
        $order->user->email([
            'identifier' => $identifier,
            'subject' => __('client.insufficient_balance_to_renew_subject', ['order' => $order->name]),
            'content' => __('client.renew_email_purpose') . '<br><br>' . __('client.insufficient_balance_to_renew_content', ['order' => $order->name, 'due_date' => $order->due_date->translatedFormat(settings('date_format', 'd M Y'))]),
            'button' => [
                'name' => __('client.add_balance'),
                'url' => route('dashboard'),
            ],
        ]);
    }

    protected function orderRenewed(Order $order)
    {
        $order->user->email([
            'subject' => __('client.order_successfully_renewed_subject', ['order' => $order->name, 'id' => $order->id]),
            'content' => __('client.renew_email_purpose') . '<br><br>' . __('client.order_successfully_renewed_content', ['order' => $order->name, 'due_date' => $order->due_date->translatedFormat(settings('date_format', 'd M Y')), 'amount' => price($order->price()->renewal_price)]),
            'button' => [
                'name' => __('client.view_order'),
                'url' => route('service', ['order' => $order->id, 'page' => 'manage']),
            ],
        ]);
    }
}
