<?php

namespace App\Http\Controllers\Client;

use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\ErrorLog;
use App\Models\Gateways\Gateway;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\Payment;
use App\Models\PaymentTax;
use App\Rules\ValidDomain;
use App\Rules\DiscordServerMembership;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class PaymentController extends Controller
{
    protected $gateway;

    public $package;

    public $packagePrice;

    public function createOrderPayment(Request $request, Package $package)
    {
        // define the package
        $this->package = $package;

        // call service before processing payment
        $package->service()->eventCheckout($package);

        $configRules = [];
        if ($package->configOptions->isNotEmpty()) {
            $configRules = $package->configOptions()->orderBy('order', 'desc')->get()->mapWithKeys(function ($option) {
                return ["custom_option.". $option->key => $option->rules()];
            })->toArray();
        }

        $rules = $package->service()->getCheckoutRules($package);
        $validated = $request->validate(array_merge([
            'domain' => ['sometimes', new ValidDomain],
        ],
            $rules, $configRules
        ));

        $price = PackagePrice::query()->where('package_id', $package->id)->findOrFail($request->input('price_id'));
        $this->packagePrice = $price;

        $gateway = Gateway::query()->findOrFail($request->input('gateway'));
        $this->gateway = $gateway;

        $ignoreRequest = ['_token', 'price_id', 'gateway', 'notes', 'country', 'zip_code', 'custom_option'];

        if (!$package->inStock()) {
            return redirect()->back()->with('error',
                trans('responses.package_limit_error',
                    ['default' => 'The package either has reached its global- or per user allowance limit.'])
            );
        }

        // Discord verification for package ID 349
        if ($package->id == 349) {
            $discordServerId = '1015093239092625410';
            $discordRule = new DiscordServerMembership($discordServerId, $package->id);

            if (!$discordRule->passes('discord_check', true)) {
                return redirect()->back()->with('error', $discordRule->message());
            }

            // Discord trust enforcement removed for HCTestDash (onboarding challenge build).
        }

        $payment = Payment::generate([
            'user_id' => auth()->user()->id,
            'type' => $request->input('gateway_type', 'once'),
            'description' => $package->name,
            'amount' => max($this->calculateTotalPrice($price->totalPrice()), 0),
            'gateway' => $gateway->toArray(),
            'package_id' => $package->id,
            'price_id' => $price->id,
            'options' => array_merge($request->except($ignoreRequest) ?? [], ['custom_option' => $validated['custom_option'] ?? []]),
            'handler' => \App\Handlers\NewOrder::class,
            'notes' => $request->input('notes'),
            'show_as_unpaid_invoice' => false,
        ]);

        $tax = (float) number_format($this->calculateTax($price->totalPrice() - $this->calculateTotalDiscount($price->totalPrice())), 2);
        if ($tax > 0) {
            PaymentTax::create([
                'payment_id' => $payment->id,
                'country' => request()->input('country'),
                'amount' => $tax,
                'included_in_price' => settings('tax_add_to_price', false),
            ]);
        }

        return redirect()->route('payment.process', ['gateway' => $gateway->id, 'payment' => $payment->id]);
    }

    // calculate the price after discounts
    protected function calculateTotalPrice($price)
    {
        $price = $price + $this->calculateCustomOptionsPrice();
        
        // Aplicar descuento/recargo por periodo
        $periodAdjustment = $this->calculatePeriodDiscount($price);
        $price = $price + $periodAdjustment;
        
        $price = $price - $this->calculateTotalDiscount($price);
    
        if (settings('tax_add_to_price')) {
            $price = $price + $this->calculateTax($price);
        }
    
        return (float) str_replace(',', '', number_format($price, 2));
    }


    // Añade este nuevo método al PaymentController.php
    protected function calculatePeriodDiscount($price)
    {
        // No hacer nada si no hay paquete o precio de paquete
        if (!$this->packagePrice || !isset($this->packagePrice->period)) {
            return 0;
        }
        
        $period = $this->packagePrice->period;
        $discount = 0;
        
        // Aplicar ajuste basado en el periodo
        switch ($period) {
            case 7: // weekly
                $discount = $price * 0.20; // +20% (surcharge)
                break;
            case 90: // quarterly
                $discount = -($price * 0.05); // -10% (discount)
                break;
            case 180: // semi-yearly
                $discount = -($price * 0.10); // -15% (discount)
                break;
            case 365: // yearly
                $discount = -($price * 0.15); // -20% (discount)
                break;
            default:
                $discount = 0; // No ajuste para otros periodos
        }
        
        return $discount;
    }
    
    protected function calculateCustomOptionsPrice()
    {
        $total = 0;
        $package = $this->package;
        $packagePrice = $this->packagePrice;
        $customOptionsModifiers = [];

        $options = request()->get('custom_option', []);
        if ($options and is_array($options)) {
            foreach ($options as $key => $value) {
                $configOption = $package->configOptions()->where('key', $key)->first();
                if ($configOption) {
                    if ($configOption->type == 'select') {
                        $selectedOption = collect($configOption->data['options'] ?? [])->firstWhere('value', $value);

                        if ($selectedOption and isset($selectedOption['monthly_price'])) {
                            $dailyPrice = $selectedOption['monthly_price'] / 30;
                            $total += $dailyPrice * $packagePrice->period;
                            // set the custom options inside the request
                            $customOptionsModifiers[] = [
                                'description' => $configOption->data['label'] ?? $configOption->key,
                                'type' => 'custom_option',
                                'key' => $configOption->key,
                                'value' => $value,
                                'daily_price' => $dailyPrice,
                            ];
                        }
                    } elseif (in_array($configOption->type, ['range', 'number']))
                    {
                        if (isset($configOption->data['monthly_price_unit'])) {
                            $dailyPrice = $configOption->data['monthly_price_unit'] / 30;
                            $total += $dailyPrice * $value * $packagePrice->period;
                            $customOptionsModifiers[] = [
                                'description' => $configOption->data['label'] ?? $configOption->key,
                                'type' => 'custom_option',
                                'key' => $configOption->key,
                                'value' => $value,
                                'daily_price' => $dailyPrice * $value,
                            ];
                        }
                    }
                }
            }
        }

        // make sure the total is not negative
        if ($total < 0) {
            $total = 0;
        }

        // Merge accumulated custom options into the request
        request()->merge(['custom_options_modifiers' => $customOptionsModifiers]);

        return $total;
    }

    protected function calculateTotalDiscount($price)
    {
        $totalDiscount = 0;
        if (session('coupon_code')) {
            $coupon = Coupon::where('code', session('coupon_code'))->first();
            if ($coupon and $coupon->isValid()) {
                $coupon->decrement('allowed_uses');
                if ($coupon->discount_type == 'percentage') {
                    $totalDiscount += $price * ($coupon->discount_amount / 100);
                } else {
                    $totalDiscount += $coupon->discount_amount;
                }
            }
        }

        // Affiliate discounts removed for HCTestDash (onboarding challenge build).

        return $totalDiscount;
    }

    protected function calculateTax($price)
    {
        if (!settings('taxes')) {
            return 0;
        }

        if (in_array($this->gateway->id, json_decode(settings('tax_disabled_gateways', '["0"]')))) {
            return 0;
        }

        $country = request()->input('country');
        $rates = config("tax.rates.$country", false);
        if ($rates) {
            $tax = 0;
            $rate = $rates['standard_rate'] / 100;
            if (settings('tax_add_to_price')) {
                $tax = $price * $rate;
            } else {
                $tax = $price - ($price / (1 + $rate));
            }

            return $tax;
        }

        return 0;
    }

    public function createOrderSubscription()
    {
        $order = Order::findOrFail(request()->get('order_id'));
        $price = $order->package->prices()->findOrFail(request()->get('price_id'));
        $payment = Payment::where('order_id', $order->id)->where('user_id', auth()->user()->id)->first();
        $gateway = Gateway::find(request()->get('gateway'));

        if ($payment == null){
            return redirect()->back();
        }
        $payment->price_id = $price->id;
        $payment->amount = $this->calculateTotalPrice($price->totalPrice());
        $payment->type = $gateway->type;
        $payment->save();

        return $this->processPayment($gateway, $payment);
    }

    public function processPayment(Gateway $gateway, Payment $payment)
    {
        // check whether the payment amount is 0, if so process the payment right away
        if ($payment->amount == 0) {
            $payment->completed();

            return redirect('/dashboard')->with('success',
                trans('responses.payment_completed_success',
                    ['default' => 'Your payment has been completed!'])
            );

        }

        try {
            $gateway_type = request()->input('gateway_type', 'once');

            return $gateway->class::processGateway($gateway, $payment, $gateway_type);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function paymentReturn(Request $request, $gateway)
    {
        try {
            $gateway = Gateway::query()->where('endpoint', $gateway)->first();

            return $gateway->class::returnGateway($request);
        } catch (Exception $error) {
            ErrorLog::catch('payment:return', $error);

            return abort('404');
        }
    }

    public function paymentCancel(Request $request, Payment $payment)
    {
        return Theme::view('store.cancel', compact('payment'));
    }

    public function paymentSuccess(Request $request, Payment $payment)
    {
        return Theme::view('store.success', compact('payment'));
    }

    public function invoice(Payment $payment)
    {
        if ($payment->status == 'unpaid' and !$payment->show_as_unpaid_invoice) {
            return redirect()->route('dashboard')->with('error',
                trans('responses.invoice_unpaid_error',
                    ['default' => 'The requested invoice is no longer available.'])
            );
        }

        return Theme::view('invoice', compact('payment'));
    }

    public function downloadInvoice(Payment $payment)
    {
        $html = view(Theme::path('invoice-pdf'), ['payment' => $payment])->render();
        $pdf = Pdf::loadHTML($html)->setOption(['defaultFont' => 'DejaVu Sans']);

        return $pdf->download("invoice-{$payment->shortId()}.pdf");
    }

    public function payInvoice(Payment $payment)
    {
        $gateway = Gateway::query()->findOrFail(request()->input('gateway'));
        $payment->update(['gateway' => $gateway->toArray()]);

        return redirect()->route('payment.process', ['gateway' => $gateway->id, 'payment' => $payment->id]);
    }

    public function createBalancePayment(Request $request)
    {
        request()->validate([
            'amount' => 'required|numeric|min:3|max:500',
        ]);

        $gateway = Gateway::query()->findOrFail($request->input('gateway'));

        $payment = Payment::generate([
            'user_id' => auth()->user()->id,
            'description' => 'Balance Top up',
            'type' => 'balance',
            'amount' => $request->input('amount'),
            'gateway' => $gateway->toArray(),
            'handler' => 'App\\Models\\Balance',
            'show_as_unpaid_invoice' => false,
        ]);

        return redirect()->route('payment.process', ['gateway' => $gateway->id, 'payment' => $payment->id]);
    }
}
