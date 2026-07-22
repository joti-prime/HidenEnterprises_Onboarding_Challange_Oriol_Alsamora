<?php

namespace App\Console\Commands;

use App\Models\Gateways\Gateway;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Console\Command;

class CheckSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update subscription statuses';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            $orders = Order::getExpiredOrders();
            $this->info("Found {$orders->count()} orders to check");

            foreach ($orders as $order) {
                try {
                    $subscription = $order->payments->sortByDesc('created_at')->firstWhere('type', 'subscription');
                    if ($subscription != null) {
                        $class = new $subscription->gateway['class'];
                        $gateway = Gateway::where('class', $subscription->gateway['class'])->first();

                        if ($class::checkSubscription($gateway, $subscription->transaction_id)) {
                            $order->extend($subscription->price['period']);
                            $this->info("Subscription with ID: {$subscription->transaction_id} successfully updated");
                            $this->extendEmail($order->user, $subscription);
                        } else {
                            $order->suspend();
                            $subscription->update(['status' => 'unpaid']);
                            $this->warn("Subscription with ID: {$subscription->transaction_id} has been canceled due to non-payment");
                            $this->suspendEmail($order->user, $subscription);
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("Error processing order ID: {$order->id} - {$e->getMessage()}");
                    ErrorLog('subscriptions:check::handle', $e->getMessage(), 'CRITICAL');
                }
            }

            $this->info('Subscription statuses checked and updated.');
        } catch (\Exception $e) {
            $this->error("An error occurred while fetching expired orders - {$e->getMessage()}");
            ErrorLog('subscriptions:check::handle', $e->getMessage(), 'CRITICAL');
        }
    }


    private function extendEmail(User $user, Payment $payment): void
    {
        app()->setLocale($user->language);
        $user->email([
            'subject' => __('client.email_subscription_payment_completed_subject'),
            'content' => emailMessage('subscription_paid', $user->language) . __('client.email_subscription_payment_content', [
                    'id' => $payment->id,
                    'currency' => $payment->currency,
                    'amount_rounded' => $payment->amount,
                    'description' => $payment->description,
                    'gateway_name' => $payment->gateway['name'],
                ]),
            'button' => [
                'name' => __('client.email_payment_completed_button'),
                'url' => route('invoice', ['payment' => $payment->id]),
            ],
        ]);
    }

    private function suspendEmail(User $user, Payment $payment): void
    {
        app()->setLocale($user->language);
        $user->email([
            'subject' => __('client.email_subscription_payment_cancel_subject'),
            'content' => emailMessage('subscription_cancel', $user->language) . __('client.email_subscription_payment_cancel_content', [
                    'id' => $payment->id,
                    'currency' => $payment->currency,
                    'amount_rounded' => $payment->amount,
                    'description' => $payment->description,
                    'gateway_name' => $payment->gateway['name'],
                ]),
            'button' => [
                'name' => __('client.email_payment_completed_button'),
                'url' => route('invoice', ['payment' => $payment->id]),
            ],
        ]);
    }
}
