<?php

namespace App\Models;

use App\Events;
use App\Events\Order\OrderEvent;
use App\Traits\Models\HasSettings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int $package_id
 * @property string $status
 * @property string $name
 * @property string|null $service
 * @property array|null $data
 * @property string|null $domain
 * @property string $type
 * @property string|null $coupon_code
 * @property int|null $period
 * @property float|array $price
 * @property float $renewal_price
 * @property float $setup_fee
 * @property float $cancellation_fee
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property string|null $cancel_reason
 * @property \Illuminate\Support\Carbon|null $last_renewed_at
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Package|null $package
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property mixed $user
 * @property mixed $options
 *
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order onlyTrashed()
 * @method static Builder|Order query()
 * @method static Builder|Order whereCancelReason($value)
 * @method static Builder|Order whereCancellationFee($value)
 * @method static Builder|Order whereCancelledAt($value)
 * @method static Builder|Order whereCouponCode($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereData($value)
 * @method static Builder|Order whereDeletedAt($value)
 * @method static Builder|Order whereDomain($value)
 * @method static Builder|Order whereDueDate($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereExternalId($value)
 * @method static Builder|Order whereLastRenewedAt($value)
 * @method static Builder|Order whereName($value)
 * @method static Builder|Order whereNotes($value)
 * @method static Builder|Order wherePackageId($value)
 * @method static Builder|Order wherePeriod($value)
 * @method static Builder|Order wherePrice($value)
 * @method static Builder|Order whereRenewalPrice($value)
 * @method static Builder|Order whereService($value)
 * @method static Builder|Order whereSetupFee($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereType($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserId($value)
 * @method static Builder|Order whereUuid($value)
 * @method static Builder|Order withTrashed()
 * @method static Builder|Order withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory, HasSettings;

    protected $table = 'orders';

    protected $fillable = [
        'uuid',
        'user_id',
        'status',
        'name',
        'service',
        'data',
        'options',
        'domain',
        'type',
        'coupon_code',
        'period',
        'package_id',
        'price',
        'renewal_price',
        'setup_fee',
        'cancellation_fee',
        'notes',
        'cancelled_at',
        'cancel_reason',
        'last_renewed_at',
        'auto_balance_renew',
        'due_date',
    ];

    protected $casts = [
        'price' => 'array',
        'data' => 'array',
        'options' => 'array',
        'last_renewed_at' => 'datetime',
        'due_date' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public static array $filters = [
        'name',
        'id',
        'external_id',
        'user_id',
        'package_id',
        'service',
        'domain',
        'price',
        'data',
        'options',
        'domain',
        'due_date',
        'cancelled_at',
        'cancel_reason',
        'last_renewed_at',
        'auto_balance_renew',
        'due_date',
        'created_at',
    ];

    protected $cachedPrice = null;

    protected $dispatchesEvents = [
        'created' => Events\Order\OrderCreated::class,
        'deleted' => Events\Order\OrderDeleted::class,
        'updated' => Events\Order\OrderUpdated::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function members()
    {
        return $this->hasMany(OrderMember::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function priceModifiers()
    {
        return $this->hasMany(OrderPriceModifier::class);
    }

    public function price(): object
    {
        if ($this->cachedPrice !== null) {
            return $this->cachedPrice;
        }

        $priceModifiers = $this->priceModifiers;


        $basePrice = $priceModifiers->sum('base_price') + ($this->price['price'] ?? 0);
        $renewalPrice = $priceModifiers->sum('daily_price') * ($this->price['period'] ?? 0) + ($this->price['renewal_price'] ?? 0);
        $cancellationFee = $priceModifiers->sum('cancellation_fee') + ($this->price['cancellation_fee'] ?? 0);
        $upgradeFee = $priceModifiers->sum('upgrade_fee') + ($this->price['upgrade_fee'] ?? 0);

        // if order is not recurring set renewal price to 0
        if (!$this->isRecurring()) {
            $renewalPrice = 0;
        }

        $this->cachedPrice = (object) [
            'base_price' => $basePrice,
            'renewal_price' => $renewalPrice,
            'setup_fee' => $this->price['setup_fee'] ?? 0,
            'cancellation_fee' => $cancellationFee,
            'upgrade_fee' => $upgradeFee,
            'period' => $this->price['period'] ?? 30,
            'period_human' => $this->period(),
            'period_to_human' => $this->periodToHuman(),
        ];

        return $this->cachedPrice;
    }

    public function canViewOrder(): bool
    {
        $authUser = auth()->user();

        // not logged in
        if (!$authUser) {
            return false;
        }

        // if user is admin
        if ($authUser->is_admin()) {
            return true;
        }

        // if authenticated users id is the same return true
        if ($authUser->id == $this->user->id) {
            return true;
        }

        // check if the user is a member
        $member = $this->members()->where('user_id', $authUser->id)->first();
        if ($member) {
            $pageOrRoute = request()->route('page') ?? request()->route()->getName();

            return $member->hasPerm($pageOrRoute);
        }

        return false;
    }

    public static function createOrder(Payment $payment)
    {
        // create the order
        if ($payment->order_id > 0) {
            $order = Order::find($payment->order_id);
        } else {
            $order = new Order;
        }

        $order->user_id = $payment->user_id;
        $order->domain = isset($payment->options['domain']) ? $payment->options['domain'] : null;
        $order->status = 'active';
        $order->name = $payment->package['name'];
        $order->service = $payment->package['service'];
        $order->options = $payment->options;
        $order->notes = $payment->notes;
        $order->package_id = $payment->package->id;
        $order->price = $payment->price;
        $order->last_renewed_at = Carbon::now()->toDateTimeString();
        $order->due_date = Carbon::now()->addDays($payment->price['period'])->toDateTimeString();
        $order->save();
        
        // Update name to include order ID
        $order->name = $payment->package['name'] . ' #' . $order->id;
        $order->save();

        $payment->order_id = $order->id;
        $payment->save();

        // reduce global quanity if its not set to -1
        if ($payment->package->global_quantity !== -1) {
            $payment->package->decrement('global_quantity');
        }

        $priceModifiers = $payment->options['custom_options_modifiers'] ?? [];

        foreach ($priceModifiers as $modifier) {
            $order->priceModifiers()->create([
                'description' => $modifier['description'],
                'type' => $modifier['type'],
                'key' => $modifier['key'],
                'value' => $modifier['value'] ?? null,
                'daily_price' => $modifier['daily_price'],
            ]);
        }

        $order->fireEvent('creation');

        return $order;
    }

    public function isRecurring(): bool
    {
        if (isset($this->price['type']) && $this->price['type'] == 'single') {
            return false;
        }

        return true;
    }

    public function period(): string
    {
        if (!$this->isRecurring()) {
            return __('admin.once');
        }

        if ($this->price['period'] == 1) {
            $period = __('admin.day');
        } elseif ($this->price['period'] == 7) {
            $period = __('admin.week');
        } elseif ($this->price['period'] == 30) {
            $period = __('admin.month');
        } elseif ($this->price['period'] == 90) {
            $period = __('admin.quarter');
        } elseif ($this->price['period'] == 180) {
            $period = __('admin.semi_year');
        } elseif ($this->price['period'] == 365) {
            $period = __('admin.year');
        } elseif ($this->price['period'] == 730) {
            $period = __('admin.per_years', ['years' => 2]);
        } elseif ($this->price['period'] == 1825) {
            $period = __('admin.per_years', ['years' => 5]);
        } elseif ($this->price['period'] == 3650) {
            $period = __('admin.per_years', ['years' => 10]);
        } else {
            $period = __('admin.day');
        }

        return $period;
    }

    public function periodToHuman(): string
    {
        if (!$this->isRecurring()) {
            return __('admin.once');
        }

        if ($this->price['period'] == 1) {
            $period = __('admin.daily');
        } elseif ($this->price['period'] == 7) {
            $period = __('admin.weekly');
        } elseif ($this->price['period'] == 30) {
            $period = __('admin.monthly');
        } elseif ($this->price['period'] == 90) {
            $period = __('admin.quarterly');
        } elseif ($this->price['period'] == 180) {
            $period = __('admin.semi_yearly');
        } elseif ($this->price['period'] == 365) {
            $period = __('admin.yearly');
        } elseif ($this->price['period'] == 730) {
            $period = __('admin.per_years', ['years' => 2]);
        } elseif ($this->price['period'] == 1825) {
            $period = __('admin.per_years', ['years' => 5]);
        } elseif ($this->price['period'] == 3650) {
            $period = __('admin.per_years', ['years' => 10]);
        } else {
            $period = __('admin.daily');
        }

        return $period;
    }

    public static function getExpiredOrders()
    {
        return self::whereStatus('active')
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::now()->subDay())
            ->get();
    }

    public function createExternalUser(array $data)
    {
        ServiceAccount::create([
            'user_id' => $data['user_id'] ?? $this->user->id,
            'order_id' => $this->id,
            'service' => $this->package->service,
            'external_id' => $data['external_id'] ?? null,
            'username' => $data['username'] ?? null,
            'password' => (isset($data['password'])) ? encrypt($data['password']) : null,
            'data' => $data['data'] ?? null,
        ]);
    }

    public function getExternalUser()
    {
        $user = ServiceAccount::where('order_id', $this->id)->orWhere('user_id', $this->user->id)->where('service', $this->package->service)->first();

        return $user ?? null;
    }

    public function hasExternalUser(): bool
    {
        if (!$this->getExternalUser()) {
            return false;
        }

        return true;
    }

    public function updateExternalPassword($password)
    {
        $this->getExternalUser()->update(['password' => encrypt($password)]);
    }

    public function getExternalId(): string
    {
        return $this->external_id ?? '';
    }

    public function setExternalId(string $id): void
    {
        $this->external_id = $id;
        $this->save();
    }

    public function service()
    {
        try {
            $service = $this->package->service()->class;

            if (!$service) {
                throw new \Exception('Could not locate Service class');
            }

            return new $service($this);

        } catch (\Exception $error) {
            // log the error
            ErrorLog::create(['source' => $this->service, 'severity' => 'ERROR', 'message' => "[HCTestDash] {$this->service} does not return a valid Service class. Make sure {$this->service} is installed and that the config contains a 'service' key returning the Service class that handles creation, suspension and termination."]);
            throw new \Exception("[HCTestDash] {$this->service} does not return a valid Service class. Make sure {$this->service} is installed and that the config contains a 'service' key returning the Service class that handles creation, suspension and termination.");
        }
    }

    public function getService()
    {
        return $this->package->service();
    }

    public function icon()
    {
        return $this->icon ? asset('storage/products/' . $this->icon) : 'https://imgur.com/LV0Lx5d.png';
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function next_invoice_date()
    {
        return $this->due_date->subDays(5);
    }

    public function cancel($cancelled_at = 'end_of_term', $reason = ''): void
    {
        if ($cancelled_at == 'immediately') {
            $this->cancelled_at = Carbon::now()->addHours(24);
            //            $this->service()->suspend();
        } else {
            $this->cancelled_at = $this->due_date;
        }

        app()->setLocale($this->user->language);
        $this->user->email([
            'subject' => __('client.email_cancel_order_subject'),
            'content' => emailMessage('cancelled', $this->user->language) .
                __('client.email_cancel_order_content', [
                    'id' => $this->id,
                    'name' => $this->name,
                    'period' => $this->cancelled_at->translatedFormat('d M Y'),
                    'reason' => $reason,
                ]),
            'button' => [
                'name' => __('client.manage_service'),
                'url' => route('dashboard'),
            ],
        ]);

        $this->status = 'cancelled';
        $this->cancel_reason = $reason;
        $this->save();

        $this->fireEvent('cancellation');

        $this->cancelGatewaySubscription();

        // dispatch cancellation event
        Events\Order\OrderCancelled::dispatch($this, $reason ?? 'no reason provided');
    }

    public function fireEvent(string $event): void
    {
        OrderEvent::handle($this, $event);
    }

    public function option($key, $default = null)
    {
        return $this->options['custom_option'][$key] ?? $this->package->data($key, $default);
    }

    public function checkoutOption($key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    public function upgrade(Package $oldPackage, Package $newPackage, PackagePrice $price)
    {
        if (!$this->getService()->canUpgrade()) {
            return 0;
        }

        if ($oldPackage->service !== $newPackage->service) {
            return 0;
        }

        $this->update([
            'name' => $newPackage->name,
            'package_id' => $newPackage->id,
            'price' => $price,
        ]);

        $this->fireEvent('upgrade');

        // prevent same package upgrade
        if ($oldPackage->id == $newPackage->id) {
            return;
        }

        try {
            $this->service()->upgrade($oldPackage, $newPackage);
        } catch (\Exception $error) {
            ErrorLog('upgrade::service', "Failed to upgrade order {$this->id} using service {$this->service}: {$error->getMessage()}");
        }

        // dispatch upgrade event
        Events\Order\OrderUpgraded::dispatch($this, $oldPackage, $newPackage);
    }

    public function extend($days = 0): void
    {
        if ($this->status == 'suspended' or $this->status == 'cancelled') {
            $this->unsuspend();
        }

        $this->due_date = $this->due_date->addDays($days);
        $this->last_renewed_at = Carbon::now();
        $this->save();

        $this->fireEvent('renewal');

        // dispatch renewal event
        Events\Order\OrderRenewed::dispatch($this);
    }

    public function create()
    {
        try {
            $this->service()->create();
        } catch (\Exception $error) {
            ErrorLog::create([
                'user_id' => $this->user->id,
                'order_id' => $this->id,
                'source' => 'server::create',
                'severity' => 'CRITICAL',
                'message' => $error->getMessage(),
            ]);
        }

        // dispatch renewal event
        Events\Order\OrderActivated::dispatch($this);
    }

    public function suspend()
    {
        $this->service()->suspend();
        $this->status = 'suspended';
        $this->save();

        $this->cancelGatewaySubscription();

        $this->fireEvent('suspension');

        // dispatch suspension event
        Events\Order\OrderSuspended::dispatch($this);
    }

    public function unsuspend()
    {
        $this->service()->unsuspend();
        $this->status = 'active';
        $this->save();

        $this->fireEvent('unsuspension');

        // dispatch unsuspension event
        Events\Order\OrderUnsuspended::dispatch($this);
    }

    public function terminate()
    {
        $this->service()->terminate();
        $this->status = 'terminated';
        $this->save();

        $this->cancelGatewaySubscription();

        $this->fireEvent('termination');

        // dispatch termination event
        Events\Order\OrderTerminated::dispatch($this);
    }

    public function forceTerminate()
    {
        try {
            $this->service()->terminate();
            $this->cancelGatewaySubscription();
        } catch (\Exception $error) {
            ErrorLog('force_terminate::service', "Failed to terminate order {$this->id} using service {$this->service}: {$error->getMessage()}");
        }

        $this->status = 'terminated';
        $this->save();

        $this->fireEvent('force_termination');

        // dispatch force termination event
        Events\Order\OrderForceTerminated::dispatch($this);
    }

    public function forceSuspend()
    {
        try {
            $this->service()->suspend();

            $this->cancelGatewaySubscription();
        } catch (\Exception $error) {
            ErrorLog('force_terminate::service', "Failed to terminate order {$this->id} using service {$this->service}: {$error->getMessage()}");
        }

        $this->status = 'suspended';
        $this->save();

        $this->fireEvent('force_suspension');

        // dispatch force suspension event
        Events\Order\OrderForceSuspended::dispatch($this);
    }

    public function isSubscription()
    {
        $subscriptionPayment = $this->payments->sortByDesc('created_at')
            ->where('status', 'paid')
            ->firstWhere('type', 'subscription');
        return $subscriptionPayment ? $subscriptionPayment->exists() : false;
    }

    public function hasSubscription(): bool
    {
        return $this->payments()->where('type', 'subscription')->exists();
    }

    public function subscriptionStatus(): bool
    {
        $subscription = $this->payments()->where('type', 'subscription')->first();

        try {
            $status = $subscription->gateway()->class::checkSubscription($subscription->gateway(), $subscription->transaction_id);
        } catch (\Exception $error) {
            $status = false;
        }

        return $status;
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->hasSubscription()) {
            if ($this->subscriptionStatus()) {
                return true;
            }
        }

        return false;
    }

    public function cancelGatewaySubscription(): void
    {
        if ($this->hasSubscription()){
            $subscription = $this->payments()->where('type', 'subscription')->first();
            if (method_exists($subscription->gateway()->class, 'cancelSubscription')) {
                $subscription->gateway()->class::cancelSubscription($subscription->gateway(), $subscription->transaction_id);
            }
        }
    }
}
