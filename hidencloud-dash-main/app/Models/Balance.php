<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

class Balance
{
    /**
     * This functions attempts to make a purchase with
     * the given amount for authenticated user
     */
    public static function attempt(Payment $payment)
    {
        $user = Auth::user();
        if ($user->balance >= $payment->amount) {
            $user->balance($payment->description, '-', $payment->amount, $payment->id);

            return true;
        }

        return false;
    }

    public static function onPaymentCompleted(Payment $payment)
    {
        $payment->user->balance('Balance Top up', '+', $payment->amount);
    }

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

    public function onPaymentRefunded(Payment $payment)
    {
        //
    }
}
