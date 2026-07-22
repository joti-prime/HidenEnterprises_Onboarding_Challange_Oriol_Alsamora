<?php

namespace App\Models\Gateways;

use App\Models\ErrorLog;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * Summary of StripeCheckoutGateway
 */
class StripeCheckoutGateway implements PaymentGatewayInterface
{
    public static function processGateway(Gateway $gateway, Payment $payment)
    {
        Stripe::setApiKey($gateway->config['publicKey']);

        $payment_types = ($payment->currency == 'EUR') ? ['card', 'ideal', 'bancontact'] : ['card'];

        $checkout = Session::create([
            'payment_method_types' => $payment_types,
            'line_items' => [[
                'price_data' => [
                    'currency' => $payment->currency,
                    'unit_amount' => $payment->amount * 100,
                    'product_data' => [
                        'name' => $payment->description,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success', ['payment' => $payment->id]),
            'cancel_url' => route('payment.cancel', ['payment' => $payment->id]),
            'metadata' => [
                'payment_id' => $payment->id,
            ],
        ]);

        return redirect($checkout->url, 303);
    }

    //       ErrorLog::catch('stripe:callback', $request);
    public static function returnGateway(Request $request)
    {
        $gateway = Gateway::where('driver', 'StripeCheckout')->firstOrFail();
        $endpoint_secret = $gateway->config['webhook_secret'];

        try {
            $payload = $request->getContent();
            $sig_header = $request->server('HTTP_STRIPE_SIGNATURE');

            $event = null;

            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );

            // Handle the specific event received
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->toArray()['object'];
                    $paymentId = $session['metadata']['payment_id'];
                    $payment = Payment::findOrFail($paymentId);
                    $payment->completed();
                    break;

                case 'payment_intent.succeeded':

                    break;
                case 'payment_intent.payment_failed':
                    // Handle failed payment
                    break;
                    // Add more cases for other events you want to handle

                default:
                    // Unexpected event type
                    return response()->json(['success' => true]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) { // SignatureVerificationException
            ErrorLog::catch('payment:stripe:callback', $e);

            return response()->json(['success' => false], 400);
        }
    }

    public static function drivers(): array
    {
        return [
            'StripeCheckout' => [
                'driver' => 'StripeCheckout',
                'type' => 'once',
                'class' => 'App\Models\Gateways\StripeCheckoutGateway',
                'endpoint' => self::endpoint(),
                'refund_support' => false, // optional
                'blade_edit_path' => 'gateways.edit.stripe_checkout_help', // optional
            ],
        ];
    }

    public static function endpoint(): string
    {
        return 'stripe-checkout';
    }

    public static function getConfigMerge(): array
    {
        return [
            'publicKey' => '',
            'webhook_secret' => '',
        ];
    }

    public static function processRefund(Payment $payment, array $data)
    {
        // TODO: Implement processRefund() method.
    }

    public static function checkSubscription(Gateway $gateway, $subscriptionId): bool
    {
        return false;
    }
}
