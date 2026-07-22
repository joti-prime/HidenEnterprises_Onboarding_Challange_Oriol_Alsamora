<?php

namespace App\Models\Gateways;

use App\Models\Balance;
use App\Models\Payment;
use Illuminate\Http\Request;

class BalanceGateway implements PaymentGatewayInterface
{
    public static function processGateway(Gateway $gateway, Payment $payment)
    {
        if (Balance::attempt($payment)) {
            $payment->completed($payment->id, []);

            return redirect()->route('payment.success', $payment->id);
        }

        $payment->failed($payment);

        return redirect()->back()->with('error', __('responses.your_payment_failed'));
    }

    public static function returnGateway(Request $request)
    {
        // not needed
    }

    public static function processRefund(Payment $payment, array $data)
    {
        $payment->user->balance('Payment Refunded', '+', $data['refunded_amount'], $payment->id);

        app()->setLocale($payment->user->language);
        $payment->user->notify([
            'type' => 'succeess',
            'icon' => '<i class="bx bxs-dollar-circle"></i>', // icon from boxicons
            'message' => __('responses.refund_payment_notify', ['payment_id' => $payment->id]),
        ]);
    }

    public static function drivers(): array
    {
        return [
            'Balance' => [
                'driver' => 'Balance',
                'type' => 'once',
                'class' => 'App\Models\Gateways\BalanceGateway',
                'endpoint' => self::endpoint(),
                'refund_support' => true,
            ],
        ];
    }

    public static function endpoint(): string
    {
        return 'balance';
    }

    public static function getConfigMerge(): array
    {
        return [];
    }

    public static function checkSubscription(Gateway $gateway, $subscriptionId): bool
    {
        return false;
    }
}
