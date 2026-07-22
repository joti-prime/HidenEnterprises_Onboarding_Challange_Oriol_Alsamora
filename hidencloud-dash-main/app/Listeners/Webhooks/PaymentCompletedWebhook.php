<?php

namespace App\Listeners\Webhooks;

use App\Events\PaymentCompleted;
use Illuminate\Support\Facades\Http;

class PaymentCompletedWebhook
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment;
        $user = $payment->user;

        $response = Http::post(settings('event_webhook_url'), [
            // Message
            'content' => 'Payment Completed',

            // Embeds Array
            'embeds' => [
                [
                    // Embed Title
                    'title' => 'Payment has been completed',

                    // add url
                    'url' => route('payments.edit', $payment->id),

                    // Embed Type
                    'type' => 'rich',

                    // Embed left border color in HEX
                    'color' => hexdec('059669'),

                    // Additional Fields array
                    'fields' => [
                        [
                            'name' => 'Payment ID',
                            'value' => "{$payment->id}",
                            'inline' => false,
                        ],
                        [
                            'name' => 'User',
                            'value' => $user->email ?? 'N/A',
                            'inline' => false,
                        ],
                        [
                            'name' => 'Description',
                            'value' => "{$payment->description}",
                            'inline' => false,
                        ],
                        [
                            'name' => 'Price',
                            'value' => price($payment->amount),
                            'inline' => false,
                        ],
                        [
                            'name' => 'Gateway',
                            'value' => $payment->gateway['name'] ?? 'N/A',
                            'inline' => false,
                        ],
                        [
                            'name' => 'Transaction ID',
                            'value' => $payment->transaction_id ?? 'N/A',
                            'inline' => false,
                        ],
                        // Etc..
                    ],
                ],
            ],

        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(PaymentCompleted $event, Throwable $exception): void
    {
        ErrorLog('PaymentCompletedWebhook', $exception->getMessage());
    }
}
