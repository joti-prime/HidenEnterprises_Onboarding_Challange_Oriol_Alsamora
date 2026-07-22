<?php

namespace App\Models\Gateways;

use App\Models\PackagePrice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaddleGateway implements PaymentGatewayInterface
{
    const SANDBOX_URL = 'https://sandbox-api.paddle.com/';

    const LIVE_URL = 'https://api.paddle.com/';

    public static $coupon = null;

    public static function processGateway(Gateway $gateway, Payment $payment)
    {
        self::createPlan($gateway, $payment);

        return redirect()->back()->with('code', self::javascript($gateway, $payment));
    }

    public static function returnGateway(Request $request)
    {
        $gateway = Gateway::where('driver', 'Paddle')->firstOrFail();
        if ($request->has('_ptxn')) {
            return redirect()->route('dashboard', ['_ptxn' => $request->get('_ptxn')])->with('code', self::updateJavascript($gateway));
        }

        // WebHook
        if (!$request->hasHeader('Paddle-Signature')){
            return response()->json(['message' => 'Signature not exist'], 403);
        }

        if (!self::webhookSignature($gateway, $request)){
            return response()->json(['message' => 'This action is not authorized, please pass a valid webhook secret'], 403);
        }

        try {
            $payment_id = $request->get('data')['custom_data']['wemx_payment_id'];
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error. The payment id was not passed through the api ($request->get(\'data\')[\'custom_data\'][\'wemx_payment_id\'])'], 403);
        }

        $payment = Payment::find($payment_id);
        if ($payment == null) {
            return response()->json(['message' => "Error. Payment ID: {$payment_id} not found"], 403);
        }

        // check if the price id of payment and paddle matches
        if ($payment->price['data']['paddle_price_id'] !== $request->get('data')['items']['0']['price']['id']) {
            return response()->json(['message' => 'Error. The payment paddle price id does not match the id from the webhook'], 403);
        }

        if ($request->get('event_type') == 'subscription.created') {
            $sub_id = $request->get('data')['id'];
            $status = $request->get('data')['status'];
            if ($status == 'active') {
                $payment->completed($sub_id, $request->all());

                return response()->json(['message' => "Success. Payment ID: {$payment_id} | Subscription ID: {$sub_id} | The order has been activated"], 200);
            }
        }

        if ($request->get('event_type') == 'transaction.completed') {
            $transaction_id = $request->get('data')['id'];
            if (array_key_exists('subscription_id', $request->get('data'))){
                if (!empty($request->get('data')['subscription_id'])){
                    return response()->json(['message' => 'Success. A cancellation transaction is defined as a subscription'], 200);
                }
            }
            $status = $request->get('data')['status'];
            if ($status == 'completed') {
                $payment->type = 'once';
                $payment->save();
                $payment->completed($transaction_id, $request->all());

                return response()->json(['message' => "Success. Payment ID: {$payment_id} | Transaction ID: {$transaction_id} | The order has been activated"], 200);
            }
        }

        return response()->json(['message' => "Error. Payment ID: {$payment_id} | Subscription ID: {$sub_id} | Subscription Status: {$status}"], 403);
    }

    public static function drivers(): array
    {
        return [
            'Paddle' => [
                'driver' => 'Paddle',
                'type' => 'subscription',
                'class' => 'App\Models\Gateways\PaddleGateway',
                'endpoint' => self::endpoint(),
                'refund_support' => false,
                'blade_edit_path' => 'gateways.edit.paddle_subscription_help', // optional
            ],
        ];
    }

    public static function endpoint(): string
    {
        return 'paddle-subscription';
    }

    public static function getConfigMerge(): array
    {
        return [
            'vendor_id' => '',
            'vendor_secret' => '',
            'webhook_secret_key' => '',
            'production' => true,
        ];
    }

    public static function processRefund(Payment $payment, array $data)
    {
        // TODO: Implement processRefund() method.
    }

    public static function cancelSubscription(Gateway $gateway, $subscriptionId): bool
    {
        if (ctype_digit($subscriptionId)) {
            // Vendor API
            $response = Http::post('https://vendors.paddle.com/api/2.0/subscription/users_cancel', [
                'vendor_id' => $gateway->config['vendor_id'],
                'vendor_auth_code' => $gateway->config['vendor_secret'],
                'subscription_id' => $subscriptionId,
            ]);

            if ($response->successful() && isset($response->json()['success']) && $response->json()['success']) {
                return true;
            } else {
                $error = $response->json()['error']['message'] ?? 'Unknown error';
                ErrorLog('paddle::subscription::cancel', "Failed to cancel subscription: $error");

                return false;
            }
        } else {
            // Platform API
            $url = filter_var($gateway->config['production'], FILTER_VALIDATE_BOOLEAN) ? self::LIVE_URL : self::SANDBOX_URL;
            $url .= "subscriptions/$subscriptionId/cancel";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $gateway->config['vendor_secret'],
            ])->asJson()->post($url, ['subscription_id' => $subscriptionId]);

            if ($response->successful()) {
                return true;
            } else {
                $error = $response->json()['error']['message'] ?? 'Unknown error';
                throw new \Exception("Failed to cancel subscription: $error");
            }
        }
    }

    public static function checkSubscription(Gateway $gateway, $subscriptionId): bool
    {
        if (ctype_digit($subscriptionId)) {
            $response = Http::post('https://vendors.paddle.com/api/2.0/subscription/users', [
                'vendor_id' => $gateway->config['vendor_id'],
                'vendor_auth_code' => $gateway->config['vendor_secret'],
                'subscription_id' => $subscriptionId,
            ]);
            $response = $response->object();
            if ($response->success and $response->response['0']->state == 'active') {
                return true;
            }
        } else {
            $url = filter_var($gateway->config['production'], FILTER_VALIDATE_BOOLEAN) ? self::LIVE_URL : self::SANDBOX_URL;
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $gateway->config['vendor_secret'],
            ])->get($url . 'subscriptions/' . $subscriptionId);

            if ($response->successful() && isset($response['data'])) {
                if ($response['data']['status'] == 'active') {
                    return true;
                }
            }
        }

        return false;
    }

    private static function createPlan(Gateway $gateway, Payment $payment): void
    {
        $price = PackagePrice::find($payment->price_id);

        if (isset($price->data['paddle_id']) and isset($price->data['paddle_price_id'])) {
            return;
        }

        $productData = [
            'name' => $payment->package->name . '/' . $price->price,
            'image_url' => asset('storage/products/' . $payment->package->icon),
            'tax_category' => 'standard',
            'custom_data' => ['package_id' => $payment->package->id, 'price_id' => $payment->price_id],
        ];

        $tmp = $price->data ?? [];
        if (!isset($tmp['paddle_price_id'])) {
            $product = self::createProduct($gateway, $price, $payment, $productData);
            $price->data = array_merge($tmp, ['paddle_id' => $product['id'], 'paddle_price_id' => $product['prices']['0']['id']]);
            $price->save();
        } else {
            $product = self::getProductById($gateway, $tmp['paddle_id']);
            if ($product == null) {
                $product = self::createProduct($gateway, $price, $payment, $productData);
                $price->data = array_merge($tmp, ['paddle_id' => $product['id'], 'paddle_price_id' => $product['prices']['0']['id']]);
                $price->save();
            }
        }

        if ((float) $product['prices']['0']['unit_price']['amount'] != ($price->price * 100)) {
            $product = self::createProduct($gateway, $price, $payment, $productData);
            $price->data = array_merge($tmp, ['paddle_id' => $product['id'], 'paddle_price_id' => $product['prices']['0']['id']]);
            $price->save();
        }

        if ($payment->getDiscountPercent() > 0) {
            self::$coupon = self::createCoupon($gateway, $payment->getDiscountPercent(), $product['prices']['0']['id'], $payment->package->name . '/' . $price->price)['id'];
        }
    }

    private static function getProductById(Gateway $gateway, $product_id)
    {
        $url = filter_var($gateway->config['production'], FILTER_VALIDATE_BOOLEAN) ? self::LIVE_URL : self::SANDBOX_URL;
        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $gateway->config['vendor_secret']])->get($url . "products/{$product_id}?include=prices");
        if ($response->successful() && isset($response['data'])) {
            return $response['data'];
        }

        return null;
    }

    private static function createProduct(Gateway $gateway, PackagePrice $price, Payment $payment, array $productDetails)
    {
        $url = filter_var($gateway->config['production'], FILTER_VALIDATE_BOOLEAN) ? self::LIVE_URL : self::SANDBOX_URL;
        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $gateway->config['vendor_secret']])->post($url . 'products', $productDetails);
        if ($response->successful() && isset($response['data'])) {
            self::createPrice($gateway, $price, $payment, $response['data']);

            return self::getProductById($gateway, $response['data']['id']);
        }

        return null;
    }

    private static function createPrice(Gateway $gateway, PackagePrice $price, Payment $payment, array $product): void
    {
        $url = filter_var($gateway->config['production'], FILTER_VALIDATE_BOOLEAN) ? self::LIVE_URL : self::SANDBOX_URL;
        $data = [
            'description' => 'Price ' . $price->package->name,
            'product_id' => $product['id'],
            'unit_price' => [
                'amount' => (string) ($price->price * 100),
                'currency_code' => $payment->currency,
            ],
            'billing_cycle' => [
                'interval' => 'day',
                'frequency' => $price->period,
            ],
            'trial_period' => null,
            'tax_mode' => 'account_setting',
            'quantity' => [
                'minimum' => 1,
                'maximum' => 999999,
            ],
        ];

        Http::withHeaders(['Authorization' => 'Bearer ' . $gateway->config['vendor_secret']])->post($url . 'prices', $data);
    }

    private static function createCoupon(Gateway $gateway, int $discountPercent, $prise_id, $description)
    {
        $url = filter_var($gateway->config['production'], FILTER_VALIDATE_BOOLEAN) ? self::LIVE_URL : self::SANDBOX_URL;
        $expires = now()->addHour()->format('Y-m-d H:i:s');
        $couponCode = strtoupper(Str::random(8));
        $data = [
            'type' => 'percentage',
            'description' => $description,
            'amount' => "$discountPercent",
            'recur' => false,
            'usage_limit' => 1,
            'enabled_for_checkout' => false,
            //            'expires_at' => $expires,
            'code' => $couponCode,
            'restrict_to' => [$prise_id],
        ];
        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $gateway->config['vendor_secret']])->post($url . 'discounts', $data);
        if ($response->successful() && isset($response['data'])) {
            return $response->json()['data'];
        }

        return null;
    }

    private static function webhookSignature(Gateway $gateway, Request $request): bool
    {
        [$tsPart, $h1Part] = explode(';', $request->header('Paddle-Signature'));
        $ts = explode('=', $tsPart)[1];
        $h1 = explode('=', $h1Part)[1];
        $payload = $request->getContent();
        $signedPayload = $ts . ':' . $payload;
        $computedHmac = hash_hmac('sha256', $signedPayload, $gateway->config['webhook_secret_key']);
        if (hash_equals($h1, $computedHmac)) {
            return true;
        }

        return false;
    }

    private static function javascript(Gateway $gateway, Payment $payment): string
    {
        $coupon = $payment->options['coupon'] ?? null;
        if (isset($payment->price['data']['paddle_price_id'])) {
            $paddle_id = $payment->price['data']['paddle_price_id'];
        } else {
            $paddle_id = false;
        }
        $route = route('payment.success', $payment->id);
        $return_route = route('payment.return', ['gateway' => $gateway->endpoint]);

        return trim("
        <script src=\"https://cdn.paddle.com/paddle/v2/paddle.js\"></script>

        <script>
            window.vendor_id = parseInt('{$gateway->config['vendor_id']}');

            if (window.vendor_id) {
                Paddle.Setup({
                    seller: window.vendor_id // replace with your Paddle seller ID
                });
            }

            if (!{$gateway->config['production']}) {
                Paddle.Environment.set('sandbox');
            }

            checkout();
            function checkout() {
                if (!'$paddle_id') {
                    alert('Paddle Product ID is not set for this product. Edit the price data and change it to {\"paddle_id\": 12345} and replace 12345 with your paddle product id');
                    return;
                }

                let itemsList = [
                  {
                    priceId: '$paddle_id',
                    quantity: 1
                  }
                ];

                let customer = {
                    email: '{$payment->user->email}',
                    address: {
                          countryCode: '{$payment->user->address->country}',
                          postalCode: '{$payment->user->address->zip_code}',
                          region: '{$payment->user->address->region}',
                          city: '{$payment->user->address->city}',
                          firstLine: '{$payment->user->address->address}'
                    }
                }

                Paddle.Checkout.open({
                      settings: {
                        displayMode: 'overlay',
                        theme: 'dark',
                        locale: '{$payment->user->language}',
                        successUrl: '$route',
                      },
                      items: itemsList,
                      customer: customer,
                      discountCode: '{$coupon}',
                      customData: {
                          wemx_payment_id: '{$payment->id}'
                      }
                });
            }

            function handlePaddleEvent(event) {
                if (event.name == 'checkout.completed.OFF') {
                    console.log('Payment completed!', event.data);
                    fetch('$return_route', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify(event.data)
                            }).then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.href = '$route';
                                } else {
                                    console.error('Server error:', data.error);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                    });

                }
                // Here you can add handlers for other events
            }
        </script>
        ");
    }

    private static function updateJavascript(Gateway $gateway): string
    {
        return trim("
        <script src=\"https://cdn.paddle.com/paddle/v2/paddle.js\"></script>

        <script>
            window.vendor_id = parseInt('{$gateway->config['vendor_id']}');

            if (!{$gateway->config['production']}) {
                Paddle.Environment.set('sandbox');
            }

            Paddle.Setup({
                seller: window.vendor_id,
                checkout: {
                  settings: {
                    displayMode: 'overlay',
                    theme: 'dark',
                    locale: 'en'
                  }
                }
              });
        </script>
        ");
    }
}
