<?php

namespace App\Models\Gateways;

use App\Facades\Theme;
use Illuminate\Http\Request;
use App\Models\Gateways\Gateway;
use App\Models\Gateways\PaymentGatewayInterface;
use App\Models\Payment;

/**
 * Summary of StripeGateway
 */
class StripeGateway implements PaymentGatewayInterface
{

    public static function processGateway(Gateway $gateway, Payment $payment)
    {
        if (!request()->input('stripeToken', false)) {
            return Theme::view('gateways.stripe-card', compact('gateway', 'payment',));
        }
        return self::proccess($gateway, $payment);
    }

    public static function returnGateway(Request $request)
    {
        // not needed
    }

    public static function drivers(): array
    {
        return [
            'Stripe' => [
                'driver' => 'Stripe',
                'type' => 'once',
                'class' => 'App\Models\Gateways\StripeGateway',
                'endpoint' => self::endpoint(),
                'refund_support' => false,
            ]
        ];
    }

    public static function endpoint(): string
    {
        return 'stripe';
    }

    public static function getConfigMerge(): array
    {
        return [
            'publicKey' => '',
        ];
    }

    private static function proccess(Gateway $gateway, Payment $payment)
    {
        $omnipayGateway = Gateway::getGateway($gateway->driver);

        $customData = [
            'user_id' => auth()->user()->id,
            'payment_id' => $payment->id,
        ];

        $response = $omnipayGateway->purchase([
            'amount' => $payment->amount,
            'token' => request()->input('stripeToken'),
            'currency' => $payment->currency,
            'description' => $payment->description,
            'metadata' => $customData
        ])->send();

        if ($response->isSuccessful()) {
            $payment->completed($response->getData()['id'], $response->getData());
            return redirect()->route("payment.success", ['payment' => $payment->id]);
        } else {
            echo "Error: " . $response->getMessage();
            return redirect()->route("payment.cancel", ['payment' => $payment->id]);
        }
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
