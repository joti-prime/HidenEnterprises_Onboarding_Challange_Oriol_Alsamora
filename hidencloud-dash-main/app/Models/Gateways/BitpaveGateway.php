<?php

namespace App\Models\Gateways;

use App\Facades\Theme;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BitpaveGateway implements PaymentGatewayInterface
{
    public static function processGateway(Gateway $gateway, Payment $payment)
    {
        if (!request()->has('session')) {
            $checkout = self::createSession($gateway, $payment);

            return redirect()->route('payment.process', ['gateway' => $gateway->id, 'payment' => $payment->id, 'session' => $checkout->session->session]);
        }

        $transaction = self::paymentStatus(request()->input('session'));
        if ($transaction->response && $transaction->status == 'completed') {
            $payment->completed($transaction->transaction->hash, $transaction->transaction);
        } elseif ($transaction->status == 'incomplete') {
            // Payment has not yet been paid and is still pending
        } elseif ($transaction->status == 'expired') {
            // Payment has expired or no longer exists, regenerate the session

        }

        $checkout = self::retrieveSession(request()->input('session'), $gateway);

        return Theme::view('gateways.bitpave.checkout', compact('gateway', 'checkout', 'payment'));
    }

    public static function returnGateway(Request $request)
    {
        // not needed
    }

    public static function drivers(): array
    {
        return [
            'Bitpave' => [
                'driver' => 'Bitpave',
                'type' => 'once',
                'class' => 'App\Models\Gateways\BitpaveGateway',
                'endpoint' => self::endpoint(),
                'refund_support' => false,
            ],
        ];
    }

    public static function endpoint(): string
    {
        return 'bitpave';
    }

    public static function getConfigMerge(): array
    {
        return [
            'client_id' => '',
            'client_secret' => '',
            'wallet' => '',
        ];
    }

    public static function createSession(Gateway $gateway, Payment $payment)
    {
        $checkout = Http::post('https://bitpave.com/api/checkout/create', [
            'client' => $gateway->config['client_id'],
            'client_secret' => $gateway->config['client_secret'],

            'name' => $payment->description,
            'icon' => 'https://pro.wemx.net/storage/products/default.png', // optional
            'wallet' => $gateway->config['wallet'],
            'price' => $payment->amount, // price in $ USD

            'success_url' => route('payment.return', ['gateway' => self::endpoint()]),
            'cancel_url' => 'https://bitpave.com?method=cancelled',
        ])->object();

        return $checkout;
    }

    public static function retrieveSession($session_uuid, Gateway $gateway)
    {
        $session = Http::post('https://bitpave.com/api/session/retrieve', [
            'client' => $gateway->config['client_id'],
            'client_secret' => $gateway->config['client_secret'],
            'session' => $session_uuid,
        ])->object();

        return $session;
    }

    public static function paymentStatus($session)
    {
        $status = Http::get("https://bitpave.com/api/session/$session/confirm")->object();

        return $status;
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
