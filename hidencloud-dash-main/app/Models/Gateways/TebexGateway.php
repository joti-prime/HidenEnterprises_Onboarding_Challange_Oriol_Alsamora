<?php

namespace App\Models\Gateways;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TebexGateway implements PaymentGatewayInterface
{
    protected static string $api_url = 'https://checkout.tebex.io/api';

    public static function processGateway(Gateway $gateway, Payment $payment)
    {
        $checkout = self::api('post', '/checkout', [
            'basket' => [
                'first_name' => $payment->user->first_name,
                'last_name' => $payment->user->last_name,
                'email' => $payment->user->email,
                'custom' => [
                    'wemx_payment_id' => $payment->id,
                    'user_id' => $payment->user->id,
                ],
                'return_url' => route('payment.cancel', $payment->id),
                'complete_url' => route('payment.success', $payment->id),
            ],
            'items' => [
                [
                    'package' => [
                        'name' => $payment->description,
                        'price' => (float) $payment->amount,
                        'metaData' => [
                            'wemx_payment_id' => $payment->id,
                            'user_id' => $payment->user->id,
                        ],
                    ],
                    'type' => 'single',
                ],
            ],
        ]);
        if (!$checkout->successful()) {
            return redirect()->back()->withError($checkout->body());
        }

        return redirect()->intended($checkout['links']['checkout']);
    }

    public static function returnGateway(Request $request)
    {
        // WebHook validation
        if ($request->get('type', 'none') == 'validation.webhook') {
            return response()->json(['id' => $request->get('id')], 200);
        }

        if (self::isSignatureValid($request)) {
            // Skip Subscription
            if ($request->get('subject')['recurring_payment_reference'] != null) {
                return response()->json(['success' => 'The event has been canceled, we are waiting for the event from the subscription'], 200);
            }

            if ($request->get('type', 'none') == 'payment.completed') {
                $transaction_id = $request->get('subject')['transaction_id'];
                $status = $request->get('subject')['status']['description'];
                $payment_id = $request->get('subject')['custom']['wemx_payment_id'];
                if ($status == 'Complete') {
                    $payment = Payment::find($payment_id);
                    $payment->completed($transaction_id, $request->all());

                    return response()->json(['success' => 'Payment completed successfully'], 200);
                } else {
                    return response()->json(['error' => "Payment status: {$status}"], 403);
                }
            }
        } else {
            return response()->json(['error' => 'WebHook signature error'], 403);
        }
    }

    public static function drivers(): array
    {
        return [
            'TebexCheckout' => [
                'driver' => 'TebexCheckout',
                'type' => 'once',
                'class' => 'App\Models\Gateways\TebexGateway',
                'endpoint' => self::endpoint(),
                'refund_support' => true,
            ],
        ];
    }

    public static function endpoint(): string
    {
        return 'tebex-checkout';
    }

    public static function getConfigMerge(): array
    {
        return [
            'username' => '',
            'password' => '',
            'webhook_key' => '',
        ];
    }

    protected static function api($method, $endpoint, $data = [])
    {
        $gateway = Gateway::getGateway('TebexCheckout');

        return Http::withBasicAuth($gateway->config()['username'], $gateway->config()['password'])->asJson()
            ->$method(self::$api_url . $endpoint, $data);
    }

    public static function processRefund(Payment $payment, array $data)
    {
        $response = self::api('post', "/payments/{$payment->transaction_id}/refund?type=txn_id", [
            'txnId' => $payment->transaction_id,
        ]);

        return $response->successful();
    }

    public static function checkSubscription(Gateway $gateway, $subscriptionId): bool
    {
        return false;
    }

    private static function isSignatureValid(Request $request): bool
    {
        $gateway = Gateway::getGateway('TebexCheckout');
        $payload = $request->getContent();
        $signature = $request->header('X-Signature', 'empty');
        $calculatedSignature = hash_hmac('sha256', hash('sha256', $payload), $gateway->config()['webhook_key']);

        return hash_equals($calculatedSignature, $signature);
    }
}
