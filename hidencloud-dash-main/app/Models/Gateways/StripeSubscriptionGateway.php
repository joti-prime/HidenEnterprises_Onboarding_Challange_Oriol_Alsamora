<?php

namespace App\Models\Gateways;

use App\Facades\Theme;
use App\Models\PackagePrice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Summary of StripeGateway
 */
class StripeSubscriptionGateway implements PaymentGatewayInterface
{
    public static function processGateway(Gateway $gateway, Payment $payment)
    {
        if (!request()->input('stripeToken', false)) {
            return Theme::view('gateways.stripe-card', compact('gateway', 'payment'));
        }

        return self::proccess($gateway, $payment);
    }

    public static function returnGateway(Request $request) {}

    public static function drivers(): array
    {
        return [
            'StripeSubscription' => [
                'driver' => 'StripeSubscription',
                'type' => 'subscription',
                'class' => 'App\Models\Gateways\StripeSubscriptionGateway',
                'endpoint' => self::endpoint(),
                'refund_support' => false,
            ],
        ];
    }

    public static function endpoint(): string
    {
        return 'stripe-subscription';
    }

    public static function getConfigMerge(): array
    {
        return [
            'stripeVersion' => '2022-11-15',
            'publicKey' => '',
            'apiKey' => '',
        ];
    }

    public static function checkSubscription(Gateway $gateway, $subscriptionId): bool
    {
        $response = Http::withToken($gateway->config['apiKey'])->get('https://api.stripe.com/v1/subscriptions/' . $subscriptionId);
        if ($response->successful()) {
            $subscription = $response->json();
            if ($subscription['status'] === 'active') {
                $invoiceId = $subscription['latest_invoice'];
                $invoiceResponse = Http::withToken($gateway->config['apiKey'])->get('https://api.stripe.com/v1/invoices/' . $invoiceId);
                if ($invoiceResponse->successful()) {
                    $invoice = $invoiceResponse->json();

                    // We check whether the invoice account has been created and whether it is awaiting payment
                    if ($invoice['status'] == 'draft') {
                        $order = Payment::where('transaction_id', $subscriptionId)->first()->order;
                        // If cache doesn't exist, add 2 hours and cache
                        if (!Cache::get('order_extended_' . $order->id)) {
                            // We add 2 hours of time to wait for the payment of the invoice
                            $order->due_date = $order->due_date->addHours(2);
                            $order->save();
                            // Save cache for 6 hours
                            Cache::put('order_extended_' . $order->id, true, now()->addHours(6));
                            exit('We stop the code so as not to stop the order prematurely');
                        }
                    }

                    if ($invoice['amount_due'] == $invoice['amount_paid']) {
                        return $invoice['status'] === 'paid';
                    }

                    return false;
                }
            }
        }

        return false;
    }

    public static function cancelSubscription(Gateway $gateway, $subscriptionId): void
    {
        try {
            Http::withToken($gateway->config['apiKey'])->asForm()
                ->delete('https://api.stripe.com/v1/subscriptions/' . $subscriptionId);
        } catch (\Exception $e) {

        }
    }

    public static function processRefund(Payment $payment, array $data)
    {
        // TODO: Implement processRefund() method.
    }

    private static function proccess(Gateway $gateway, Payment $payment)
    {
        $apiKey = $gateway->config['apiKey'];
        $customer = self::getCustomer($gateway, $payment, $apiKey);
        $product_id = self::getProductId($payment, $apiKey);
        $price = self::createPrice($payment, $product_id, $apiKey);
        $coupon = null;
        if ($payment->getDiscountPercent() > 0) {
            $coupon = self::createCoupon($payment->getDiscountPercent(), $apiKey)['id'];
        }
        $subscription = self::createSubscription($customer, $price['id'], $apiKey, $coupon);
        if (self::checkSubscription($gateway, $subscription['id'])){
            $payment->completed($subscription['id'], $subscription);

            return redirect(route('payment.success', $payment->id));
        }

        return redirect(route('payment.cancel', $payment->id));
    }

    private static function createSubscription($customer, $price_id, $apiKey, $coupon = null)
    {
        $data = [
            'customer' => $customer['id'],
            'items' => [['price' => $price_id]],
            'default_payment_method' => $customer['invoice_settings']['default_payment_method'],
            'coupon' => $coupon,
        ];

        return self::sendPostRequest('https://api.stripe.com/v1/subscriptions', $data, $apiKey);
    }

    private static function getProductId(Payment $payment, $apiKey)
    {
        $price = PackagePrice::find($payment->price_id);
        $tmp = $price->data ?? [];
        if (array_key_exists('stripe_product_id', $tmp)) {
            $product = self::getProduct($tmp['stripe_product_id'], $apiKey);
            if ($product != null) {
                return $product['id'];
            }
        }
        $product = self::createProduct($payment->package->name . '/' . $payment->price->renewal_price, $apiKey);
        $price->data = array_merge($tmp, ['stripe_product_id' => $product['id']]);
        $price->save();

        return $product['id'];
    }

    private static function getProduct($product_id, $apiKey)
    {
        $response = Http::withToken($apiKey)->asForm()
            ->get('https://api.stripe.com/v1/products/' . $product_id);
        if ($response->successful()) {
            return $response->json();
        }

        return null;

    }

    private static function createProduct($name, $apiKey)
    {
        $data = ['name' => $name];

        return self::sendPostRequest('https://api.stripe.com/v1/products', $data, $apiKey);
    }

    private static function createPrice(Payment $payment, $product_id, $apiKey)
    {
        $data = [
            'product' => $product_id,
            'unit_amount' => $payment->price->renewal_price * 100,
            'currency' => $payment->currency,
            'recurring' => [
                'interval' => 'day',
                'interval_count' => $payment->price->period,
            ],
        ];

        return self::sendPostRequest('https://api.stripe.com/v1/prices', $data, $apiKey);
    }

    private static function createCoupon($percent_off, $apiKey)
    {
        $couponData = [
            'percent_off' => $percent_off,
            'duration' => 'once',
            'redeem_by' => time() + 3600,
            'max_redemptions' => 1,
        ];

        return self::sendPostRequest('https://api.stripe.com/v1/coupons', $couponData, $apiKey);
    }

    private static function getCustomer(Gateway $gateway, Payment $payment, $apiKey)
    {
        $customer = self::searchCustomer($gateway, $payment->user->email);
        if ($customer != null) {
            self::setPaymentMethod($gateway, request()->input('stripeToken'), $customer['id']);

            return $customer;
        }
        $customerData = [
            'name' => $payment->user->username,
            'email' => $payment->user->email,
            'description' => 'Wemx customer: ' . $payment->user->email,
            'address' => [
                'line1' => $payment->user->address->address,
                'city' => $payment->user->address->city,
                'state' => $payment->user->address->region,
                'postal_code' => $payment->user->address->zip_code,
                'country' => $payment->user->address->country,
            ],
            'metadata' => [
                'user_id' => $payment->user->id,
            ],
        ];
        $response = self::sendPostRequest('https://api.stripe.com/v1/customers', $customerData, $apiKey);
        self::setPaymentMethod($gateway, request()->input('stripeToken'), $response['id']);

        return self::searchCustomer($gateway, $payment->user->email);
    }

    private static function searchCustomer(Gateway $gateway, $email)
    {
        $response = Http::withToken($gateway->config['apiKey'])->asForm()
            ->get('https://api.stripe.com/v1/customers/search', [
                'query' => "email:'{$email}'",
            ]);
        if ($response->successful()) {
            if (array_key_exists(0, $response->json()['data'])) {
                return $response->json()['data']['0'];
            }
        }

        return null;
    }

    private static function setPaymentMethod(Gateway $gateway, string $tokenId, string $customerId): void
    {
        $methods = Http::withToken($gateway->config['apiKey'])->asForm()->get("https://api.stripe.com/v1/customers/{$customerId}/payment_methods");
        if ($methods->successful()) {
            $methods = $methods->json();
            if (!empty($methods['data'])) {
                foreach ($methods['data'] as $method) {
                    if ($method['type'] == 'card') {
                        self::attachPaymentMethod($gateway, $customerId, $method['id'], true);

                        return;
                    }
                }
            }
        }

        try {
            // Create a PaymentMethod from the token
            $response = Http::withToken($gateway->config['apiKey'])->asForm()
                ->post('https://api.stripe.com/v1/payment_methods', [
                    'type' => 'card',
                    'card[token]' => $tokenId,
                ]);
            if (!$response->successful()) {
                throw new \Exception('Error creating payment method: ' . $response->body());
            }
            self::attachPaymentMethod($gateway, $customerId, $response->json()['id']);
        } catch (\Exception $e) {
            throw new \Exception('Error attaching payment method: ' . $e->getMessage());
        }
    }

    private static function attachPaymentMethod(Gateway $gateway, $customer_id, $method_id, $isset = false): void
    {
        if (!$isset) {
            // Attach the PaymentMethod to the customer
            Http::withToken($gateway->config['apiKey'])->asForm()
                ->post("https://api.stripe.com/v1/payment_methods/{$method_id}/attach", [
                    'customer' => $customer_id,
                ]);
        }
        // Set payment method as default for customer
        Http::withToken($gateway->config['apiKey'])->asForm()
            ->post("https://api.stripe.com/v1/customers/{$customer_id}", [
                'invoice_settings' => [
                    'default_payment_method' => $method_id,
                ],
            ]);
    }

    private static function sendPostRequest($url, $data, $apiKey)
    {
        try {
            $response = Http::withToken($apiKey)->asForm()->post($url, $data);
            if ($response->successful()) {
                return $response->json();
            } else {
                throw new \Exception('Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            throw new \Exception('Error: ' . $e->getMessage());
        }
    }
}
