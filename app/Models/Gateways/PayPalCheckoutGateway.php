<?php

namespace App\Models\Gateways;

use App\Models\ErrorLog;
use App\Models\Payment;
use Illuminate\Http\Request;

class PayPalCheckoutGateway implements PaymentGatewayInterface
{
    public static function processGateway(Gateway $gateway, Payment $payment)
    {
        if ($gateway->config['production'] == 'true') {
            $url = 'https://ipnpb.paypal.com/cgi-bin/webscr';
        } else {
            $url = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
        }

        echo '<body onload="document.redirectform.submit()" style="display: none">
            <form action="'. $url .'" method="post" name="redirectform">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="business" value="'. $gateway->config['paypal_email'] .'">
                <input type="hidden" name="item_name" value="'.$payment->description.'">
                <input type="hidden" name="item_number" value="'.$payment->id.'">
                <input type="hidden" name="amount" value="'.$payment->amount.'">
                <input type="hidden" name="currency_code" value="'.$payment->currency.'">
                <input name="cancel_return" value="' . route('payment.cancel', ['payment' => $payment->id]) . '">
                <input name="notify_url" value="' . route('payment.return', ['gateway' => self::endpoint(), 'payment' => $payment->id]) . '">
                <input name="return" value="' . route('payment.success', ['payment' => $payment->id]) . '">
                <input name="rm" value="2">
                <input name="charset" value="utf-8">
                <input name="no_note" value="1">
              </form>
        </body>';
    }

    public static function returnGateway(Request $request)
    {
        try {
            $payment = Payment::findOrFail($request->input('payment'));
            $gateway = Gateway::where('driver', 'PayPalCheckout')->firstOrFail();

            // The IPN request is a POST request, so we'll get the data from the request input
            $ipnPayload = $request->all();

            ErrorLog('payment:return:paypal-checkout:payload', json_encode($ipnPayload));

            // Before processing the IPN message, you should validate it to make sure it's actually from PayPal
            $ipnCheck = self::validateIpn($ipnPayload);

            if ($ipnCheck) {
                // Process IPN message
                $paymentStatus = $ipnPayload['payment_status'];

                if ($paymentStatus == 'Completed') {
                    // compare the payment amount sent with the amount from the database
                    if ($ipnPayload['mc_gross'] != $payment->amount) {
                        // The payment amount doesn't match the amount from the database
                        // Log for manual investigation
                        ErrorLog::catch('payment:return:paypal-checkout:amount-mismatch', "Amount mismatch for payment {$payment->id}");
                        return;
                    }

                    // Compare item number
                    if ($ipnPayload['item_number'] != $payment->id) {
                        ErrorLog::catch('payment:return:paypal-checkout:itemnumber-mismatch', "Item number mismatch for payment {$payment->id} - {$ipnPayload['item_number']}");
                        return;
                    }

                    // check if the receiver email is the same as the one in the database
                    if ($ipnPayload['receiver_email'] !== $gateway->config['paypal_email']) {
                        ErrorLog::catch('payment:return:paypal-checkout:receiver-mismatch', "Receiver email mismatch for payment {$payment->id} - {$ipnPayload['receiver_email']}");
                        return;
                    }

                    // check if the currency is the same as the one in the database
                    if ($ipnPayload['mc_currency'] !== $payment->currency) {
                        ErrorLog::catch('payment:return:paypal-checkout:currency-mismatch', "Currency mismatch for payment {$payment->id} - {$ipnPayload['mc_currency']}");
                        return;
                    }

                    // check if the transaction is already processed
                    if (Payment::where('transaction_id', $ipnPayload['txn_id'])->exists()) {
                        ErrorLog::catch('payment:return:paypal-checkout:duplicate-txn', "Duplicate transaction ID for payment {$payment->id} - {$ipnPayload['txn_id']}");
                        return;
                    }

                    // Your code to handle successful payment
                    $payment->completed($ipnPayload['txn_id'], $ipnPayload);
                } else {
                    // Your code to handle failed payment
                }
            }

        } catch (\Exception $error) {
            ErrorLog::catch('payment:return:paypal-checkout:failed', $error);
        }
    }

    public static function drivers(): array
    {
        return [
            'PayPalCheckout' => [
                'driver' => 'PayPalCheckout',
                'type' => 'once',
                'class' => 'App\Models\Gateways\PayPalCheckoutGateway',
                'endpoint' => self::endpoint(),
                'refund_support' => false,
            ],
        ];
    }

    private static function validateIpn($ipnPayload)
    {
        // This is the URL you'll post the IPN message back to for validation
        $gateway = Gateway::where('driver', 'PayPalCheckout')->firstOrFail();

        if ($gateway->config['production'] == 'true') {
            $paypalUrl = 'https://ipnpb.paypal.com/cgi-bin/webscr';
        } else {
            $paypalUrl = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
        }

        $payload = 'cmd=_notify-validate';

        foreach ($ipnPayload as $key => $value) {
            $value = urlencode($value);
            $payload .= "&$key=$value";
        }

        // Use CURL to post back the data for validation
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $paypalUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $result = curl_exec($ch);
        curl_close($ch);

        return strcmp($result, 'VERIFIED') == 0;
    }

    public static function endpoint(): string
    {
        return 'paypal-checkout';
    }

    public static function getConfigMerge(): array
    {
        return [
            'paypal_email' => '',
            'production' => true,
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