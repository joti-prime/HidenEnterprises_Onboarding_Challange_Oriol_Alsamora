<?php

namespace App\Models;

use App\Events;
use App\Facades\Theme;
use App\Models\Gateways\Gateway;
use App\Traits\Models\HasSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * App\Models\Payment
 *
 * @property int $id
 * @property int $user_id
 * @property string $transaction_id
 * @property string|null $gateway
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment where($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUserId($value)
 *
 * @property int|null $order_id
 * @property string|null $type
 * @property string|null $description
 * @property string $status
 * @property string $currency
 * @property float $amount
 * @property string|null $service_handler
 * @property array|null $price
 * @property array|null $package
 * @property string|null $notes
 * @property mixed $due_date
 * @property mixed $show_as_unpaid_invoice
 * @property mixed $order
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePackage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereServiceHandler($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereStatus($value)
 *
 * @mixin \Eloquent
 */
class Payment extends Model
{
    use HasFactory, HasSettings;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'order_id',
        'package_id',
        'price_id',
        'type',
        'description',
        'status',
        'currency',
        'amount',
        'transaction_id',
        'handler',
        'gateway',
        'notes',
        'data',
        'options',
        'show_as_unpaid_invoice',
    ];

    protected $casts = [
        'gateway' => 'array',
        'data' => 'array',
        'options' => 'array',
        'due_date' => 'datetime',
    ];

    public static array $filters = [
        'id',
        'description',
        'package_id',
        'price_id',
        'user_id',
        'order_id',
        'status',
        'type',
        'currency',
        'amount',
        'transaction_id',
        'gateway',
        'data',
        'options',
    ];

    protected $dispatchesEvents = [
        'created' => Events\PaymentCreated::class,
        'updated' => Events\PaymentUpdated::class,
        'deleted' => Events\PaymentDeleted::class,
    ];

    public static function getExpiredPayments()
    {
        return self::whereStatus('unpaid')->whereNotNull('due_date')->where('due_date', '<', Carbon::now())->get();
    }

    public function scopeGetAmountSum($query)
    {
        return $query->sum('amount');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class)->with('category');
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(PackagePrice::class);
    }

    public function tax(): HasOne
    {
        return $this->hasOne(PaymentTax::class);
    }

    public function gateway()
    {
        return Gateway::where('name', $this->gateway['name'])->first();
    }

    public function handler()
    {
        return new $this->handler;
    }

    public function shortId()
    {
        return substr($this->id, 0, 8);
    }

    public function completed($transaction_id = null, $data = [])
    {
        try {
            if ($this->type == 'once' and $this->status == 'paid'){
                return 0;
            }

            // call handler
            if (isset($this->handler)) {
                $handler = new $this->handler;
                $handler->onPaymentCompleted($this);
            }

            $this->type = $this->gateway['type'] ?? 'once';
            $this->status = 'paid';
            $this->transaction_id = $transaction_id;
            $this->data = $data;
            $this->save();

            // Affiliates module removed for HCTestDash (onboarding challenge build).

            // save invoice
            $invoice = Pdf::loadView(Theme::path('invoice-pdf'), ['payment' => $this]);
            $path = "attachments/invoices/invoice-{$this->shortId()}.pdf";
            Storage::put($path, $invoice->output());

            // send email
            app()->setLocale($this->user->language);
            $this->user->email([
                'subject' => __('client.email_payment_completed_subject'),
                'content' => emailMessage('payment_paid', $this->user->language) . __('client.email_payment_completed_content', [
                    'id' => $this->id,
                    'currency' => $this->currency,
                    'amount_rounded' => price($this->amount),
                    'description' => $this->description,
                    'gateway_name' => $this->gateway['name'],
                ]),
                'button' => [
                    'name' => __('client.email_payment_completed_button'),
                    'url' => route('invoice', ['payment' => $this->id]),
                ],
                'attachment' => [
                    [
                        'type' => 'application/pdf',
                        'name' => "invoice-{$this->shortId()}.pdf",
                        'path' => $path,
                    ],
                ],
            ]);

            // create notification
            $this->user->notify([
                'type' => 'success',
                'icon' => "<i class='bx bx-receipt'></i>",
                'message' => __('responses.payment_completed_success'),
                'button_url' => route('invoice', ['payment' => $this->id]),
            ]);

            // dispatch payment completed event
            Events\PaymentCompleted::dispatch($this);

        } catch (\Exception $error) {
            ErrorLog("{$this->id}::payment::completion::failed", $error);
        }
    }

    public function failed()
    {
        try {
            if (isset($this->handler)) {
                $handler = new $this->handler;
                $handler->onPaymentFailed($this);
            }
        } catch (\Exception $error) {
            ErrorLog("{$this->id}::payment::fail::failed", $error);
        }
    }

    public function refunded($refunded_amount, $cancel_service = false)
    {
        try {
            if (isset($this->handler)) {
                $handler = new $this->handler;
                $refund = 'onPaymentRefunded';
            }

            if (method_exists($handler, $refund)) {
                $handler->$refund($this);
            }

            // send email
            app()->setLocale($this->user->language);
            $this->user->email([
                'subject' => __('client.email_payment_refunded_subject'),
                'content' => emailMessage('refund', $this->user->language) . __('client.email_payment_refunded_content', [
                    'description' => $this->description,
                    'amount_rounded' => price($this->amount),
                    'id' => $this->id,
                    'currency' => $this->currency,
                    'gateway_name' => $this->gateway['name'],
                ]),
                'button' => [
                    'name' => __('client.email_payment_completed_button'),
                    'url' => route('invoice', ['payment' => $this->id]),
                ],
            ]);

            // dispatch payment completed event
            Events\PaymentRefunded::dispatch($this, $refunded_amount);

        } catch (\Exception $error) {
            // catch that the service handler returned an error
            ErrorLog("{$this->id}::payment::refund::failed", $error);
        }

        try {
            $gateway = new $this->gateway['class'];
            $gateway->processRefund($this, ['refunded_amount' => $refunded_amount]);
        } catch (\Exception $error) {
            ErrorLog("{$this->id}::payment:::gateway:refund::failed", $error);
        }

        $this->status = 'refunded';
        $this->save();
    }

    public static function generate(array $data)
    {
        $payment = new Payment;
        $payment->id = Str::uuid()->toString();
        $payment->user_id = $data['user_id'];
        $payment->order_id = (isset($data['order_id'])) ? $data['order_id'] : null;
        $payment->package_id = (isset($data['package_id'])) ? $data['package_id'] : null;
        $payment->price_id = (isset($data['price_id'])) ? $data['price_id'] : null;
        $payment->type = (isset($data['type'])) ? $data['type'] : 'once';
        $payment->description = $data['description'];
        $payment->status = (isset($data['status'])) ? $data['status'] : 'unpaid';
        $payment->currency = (isset($data['currency'])) ? $data['currency'] : settings('currency', 'USD');
        $payment->amount = $data['amount'];
        $payment->handler = (isset($data['handler'])) ? $data['handler'] : null;
        $payment->show_as_unpaid_invoice = (isset($data['show_as_unpaid_invoice'])) ? $data['show_as_unpaid_invoice'] : true;
        $payment->gateway = (isset($data['gateway'])) ? $data['gateway'] : null;
        $payment->notes = (isset($data['notes'])) ? $data['notes'] : null;
        $payment->options = (isset($data['options'])) ? $data['options'] : null;
        $payment->due_date = (isset($data['due_date'])) ? $data['due_date'] : null;
        $payment->save();

        return $payment;
    }

    public function getDiscountPercent(){
        $totalDiscount = 0;
        if (session('coupon_code')) {
            $coupon = Coupon::where('code', session('coupon_code'))->first();
            if ($coupon->discount_type == 'percentage') {
                $totalDiscount = $coupon->discount_amount;
            }
        }
        // Affiliate discounts removed for HCTestDash (onboarding challenge build).

        return $totalDiscount;
    }
}
