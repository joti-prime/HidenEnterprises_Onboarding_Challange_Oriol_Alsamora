<?php

namespace App\Models\Gateways;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TebexSubscriptionGateway implements PaymentGatewayInterface
{
    protected static string $api_url = 'https://checkout.tebex.io/api';

    public static function processGateway(Gateway $gateway, Payment $payment)
    {
        $subscriptionData = [
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
                        'price' => (float) $payment->price->renewal_price,
                        'expiry_period' => 'day',
                        'expiry_length' => (int) $payment->price->period,
                        'metaData' => [
                            'wemx_payment_id' => $payment->id,
                            'user_id' => $payment->user->id,
                        ],
                    ],
                    'type' => 'subscription',
                ],
            ],
        ];

        if (session('coupon_code')) {
            $subscriptionData['sale'] = [
                'name' => session('coupon_code'),
                'discount_type' => 'percentage',
                'amount' => (float) $payment->getDiscountPercent(),
            ];
        }

        $subscription = self::api('post', '/checkout', $subscriptionData);
        if (!$subscription->successful()) {
            return redirect()->back()->withError($subscription->body());
        }

        return redirect()->intended($subscription['links']['checkout']);
    }

    public static function returnGateway(Request $request)
    {
        // WebHook validation
        if ($request->get('type', 'none') == 'validation.webhook') {
            return response()->json(['id' => $request->get('id')], 200);
        }

        if (self::isSignatureValid($request)){
            if ($request->get('type', 'none') == 'recurring-payment.started') {
                $transaction_id = $request->get('subject')['reference'];
                $status = $request->get('subject')['status']['description'];
                $payment_id = $request->get('subject')['initial_payment']['custom']['wemx_payment_id'];
                if ($status == 'Active') {
                    $payment = Payment::find($payment_id);
                    $payment->completed($transaction_id, $request->all());

                    if ($payment->user) {
                        $payment->user->email([
                            'subject' => __('client.your_platform_subscription_subject', ['platform' => 'Tebex']),
                            'content' => __('client.your_platform_subscription_content', ['platform' => 'Tebex', 'description' => $payment->description]),
                            'button' => [
                                'name' => __('client.manage_subscription'),
                                'url' => 'https://checkout.tebex.io/payment-history',
                            ],
                        ]);
                    }

                    return response()->json(['success' => 'Subscription completed successfully'], 200);
                } else {
                    return response()->json(['error' => "Subscription status: {$status}"], 403);
                }
            }
        } else {
            return response()->json(['error' => 'WebHook signature error'], 403);
        }

        return response()->json(['data' => $request->all()], 200);
    }

    public static function drivers(): array
    {
        return [
            'TebexSubscription' => [
                'driver' => 'TebexSubscription',
                'type' => 'subscription',
                'class' => 'App\Models\Gateways\TebexSubscriptionGateway',
                'endpoint' => self::endpoint(),
                'refund_support' => false,
            ],
        ];
    }

    public static function endpoint(): string
    {
        return 'tebex-subscription';
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
        $gateway = Gateway::getGateway('TebexSubscription');

        return Http::withBasicAuth($gateway->config()['username'], $gateway->config()['password'])->asJson()
            ->$method(self::$api_url . $endpoint, $data);
    }

    public static function processRefund(Payment $payment, array $data) {}

    public static function checkSubscription(Gateway $gateway, $subscriptionId): bool
    {
        $response = self::api('get', "/recurring-payments/{$subscriptionId}");

        return $response->successful() && $response['status']['description'] === 'Active';
    }

    public static function cancelSubscription(Gateway $gateway, $subscriptionId): void
    {
        self::api('delete', "/recurring-payments/{$subscriptionId}");
    }

    private static function isSignatureValid(Request $request): bool
    {
        $gateway = Gateway::getGateway('TebexSubscription');
        $payload = $request->getContent();
        $signature = $request->header('X-Signature', 'empty');
        $calculatedSignature = hash_hmac('sha256', hash('sha256', $payload), $gateway->config()['webhook_key']);

        return hash_equals($calculatedSignature, $signature);
    }
}
