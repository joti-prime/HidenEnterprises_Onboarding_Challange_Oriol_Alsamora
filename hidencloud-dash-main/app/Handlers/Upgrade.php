<?php

namespace App\Handlers;

use App\Facades\ServiceHandler;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\Payment;

class Upgrade extends ServiceHandler
{
    /**
     * This event will be fired once the payment is completed
     */
    public function onPaymentCompleted(Payment $payment)
    {
        $newPackage = Package::findOrFail($payment->options['package_id']);
        $price = PackagePrice::findOrFail($payment->options['price_id']);
        $payment->order->upgrade($payment->order->package, $newPackage, $price);
    }

    /**
     * This event will be fired once the payment is completed
     */
    public function onPaymentFailed(Payment $payment)
    {
        //
    }

    public function onPaymentPending(Payment $payment)
    {
        //
    }

    public function onPaymentDeclined(Payment $payment)
    {
        //
    }

    public function onPaymentExpired(Payment $payment)
    {
        //
    }
}
