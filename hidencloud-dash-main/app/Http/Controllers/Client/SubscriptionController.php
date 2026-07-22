<?php

namespace App\Http\Controllers\Client;

use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\Gateways\Gateway;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions_paddle = Subscription::query()->whereGateway('Paddle')->where('user_id', Auth::user()->id)->latest()->paginate(10);

        return Theme::view('subscriptions.index', compact('subscriptions_paddle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'price_id' => 'required',
            'gateway' => 'required|max:255',
        ]);

        $order = auth()->user()->orders()->findOrFail($request->input('order_id'));
        $price = $order->package->prices()->findOrFail($request->input('price_id'));
        $gateway = Gateway::query()->whereType('subscription')->findOrFail($request->input('gateway'));

        $payment = Payment::generate([
            'user_id' => auth()->user()->id,
            'order_id' => $order->id,
            'type' => 'subscription',
            'description' => $order->name,
            'amount' => $price->renewal_price,
            'currency' => Gateway::$currency,
            'gateway' => $gateway->toArray(),
            'package_id' => $price->package->id,
            'price_id' => $price->id,
            'handler' => 'App\\Http\\Controllers\\Client\\SubscriptionController',
            'show_as_unpaid_invoice' => false,
        ]);

        return redirect()->route('payment.process', ['gateway' => $gateway->id, 'payment' => $payment->id]);
    }

    public static function getPricesForPackage($order_id)
    {
        return auth()->user()->orders()->whereStatus('active')->whereId($order_id)->firstOrFail()->package->prices;
    }

    public static function onPaymentCompleted(Payment $payment): void
    {
        $payment->order->extend($payment->price['period']);
    }
}
