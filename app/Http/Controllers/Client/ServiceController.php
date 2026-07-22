<?php

namespace App\Http\Controllers\Client;

use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\EmailHistory;
use App\Models\Order;
use App\Models\OrderMember;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\Payment;
use App\Models\User;
use App\Rules\DiscordServerMembership;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ServiceController extends Controller
{
    /**
     * This is the main service function that returns the requested resource
     * by default 'manage' method is returned
     *
     * @return Renderable
     */
    public function service(Order $order, $page = 'manage')
    {
        // define order inside the request
        request()->order = $order;

        if ($page == 'invoices') {
            return self::invoices($order);
        }

        if ($page == 'members') {
            return self::members($order);
        }

        if ($page == 'invite-member') {
            return self::inviteMember($order);
        }

        if ($page == 'update-member') {
            return self::updateMember($order);
        }

        if ($page == 'delete-member') {
            return self::deleteMember($order);
        }

        if ($page == 'renew') {
            return self::renew($order);
        }

        if ($page == 'cancel-service') {
            return self::cancel($order);
        }

        if ($page == 'delete-service') {
            return self::deleteService($order);
        }

        if ($page == 'cancel-undo') {
            return self::undoCancel($order);
        }

        if ($page == 'upgrade') {
            return self::upgrade($order);
        }

        if ($page == 'balance-renew') {
            return self::toggleBalanceRenew($order);
        }

        if ($page == 'login-to-panel') {
            return self::loginToPanel($order);
        }

        $serviceClass = $order->getService();
        if ($page == 'change-password' && $serviceClass->canChangePassword()) {
            $validated = request()->validate([
                'password' => ['required', 'confirmed'],
            ]);

            return $serviceClass->class->changePassword($order, request()->input('password'));
        }

        if ($service_page = $serviceClass->pages()->get($page)) {
            return call_user_func([$serviceClass->class, $service_page], $order);
        }

        return self::manage($order);
    }


    /**
     * Manage returns the index page of your service
     *
     * @return Renderable
     */
    public function manage(Order $order)
    {
        return Theme::view('orders.manage', compact('order'));
    }

    /**
     * Invoices returns the path to your service invoices
     *
     * @return Renderable
     */
    public function invoices(Order $order)
    {
        return Theme::view('orders.invoices', compact('order'));
    }

    /**
     * Return the members page
     *
     * @return view
     */
    public function members(Order $order)
    {
        return Theme::view('orders.members', compact('order'));
    }

    /**
     * Handle the invite member
     *
     * @return view
     */
    public function inviteMember(Order $order)
    {
        $validated = request()->validate([
            'email' => 'required|email',
            'is_admin' => 'required|boolean',
            'permissions' => 'nullable|array',
        ]);

        if ($validated['email'] == auth()->user()->email or $validated['email'] == $order->user->email) {
            return redirect()->back()->withError('You can\'t invite yourself or the service owner');
        }

        if ($order->members()->where('email', $validated['email'])->exists()) {
            return redirect()->back()->withError('This user is already a member for this order');
        }

        $member = OrderMember::create([
            'inviter_id' => auth()->user()->id,
            'order_id' => $order->id,
            'email' => $validated['email'],
            'is_admin' => $validated['is_admin'],
            'permissions' => $validated['permissions'] ?? [],
        ]);

        $this->emailInvite($member);

        return redirect()->back()->withSuccess("We have emailed the invite to {$validated['email']}");
    }

    protected function emailInvite($member)
    {
        $user = User::where('email', $member->email)->first();

        if ($user) {
            return $user->email([
                'subject' => __('client.member_invite_email_subject', ['user' => ucfirst($member->inviter->username), 'service' => $member->order->name]),
                'content' => __('client.member_invite_email_content', ['user' => ucfirst($member->inviter->username), 'service' => $member->order->name]),
                'button' => [
                    'name' => __('client.view_invitation'),
                    'url' => route('invites.index'),
                ],
            ]);
        }

        return EmailHistory::query()->create([
            'user_id' => null,
            'sender' => config('mail.from.address'),
            'receiver' => $member->email,
            'subject' => __('client.member_invite_email_subject', ['user' => ucfirst($member->inviter->username), 'service' => $member->order->name]),
            'content' => __('client.member_invite_email_content', ['user' => ucfirst($member->inviter->username), 'service' => $member->order->name]).
            __('client.member_invite_email_content_guest', ['email' => $member->email]),
            'button' => [
                'name' => __('client.view_invitation'),
                'url' => route('invites.index'),
            ],
            'attachment' => null,
        ]);
    }

    public function updateMember(Order $order)
    {
        $validated = request()->validate([
            'is_admin' => 'required|boolean',
            'permissions' => 'nullable|array',
        ]);

        $member = $order->members()->findOrFail(request()->get('member_id'));
        $member->update([
            'is_admin' => $validated['is_admin'],
            'permissions' => $validated['permissions'] ?? [],
        ]);

        return redirect()->back()->withSuccess('Updated member successfully');
    }

    public function deleteMember(Order $order)
    {
        $member = $order->members()->findOrFail(request()->input('member_id'));
        $member->delete();

        return redirect()->back()->withSuccess('Member was removed successfully');
    }

    /**
     * This function manages renewals
     */
    public function renew(Order $order)
    {
        $validated = request()->validate([
            'days' => "required_if:custom_date,null|integer|min:{$order->price()->period}|max:". 12 * $order->price()->period,
            'custom_date' => 'required_if:days,null|date|after:today',
        ]);

        // check if order is recurring
        if (!$order->isRecurring()) {
            return redirect()->back()->with('error', 'This order is not recurring');
        }

        // Server-side renewal restriction for free services (FreePterodactyl)
        // Free services can only be renewed when there is less than 1 day left before expiration
        if ($order->package && $order->package->service === 'freepterodactyl') {
            $daysUntilExpiration = now()->diffInDays($order->due_date, false);
            if ($daysUntilExpiration > 1) {
                return redirect()->back()->with('error', "You can only renew your free service when there is less than 1 day left before it expires. Your service expires in {$daysUntilExpiration} days.");
            }

            // Require Discord server membership to renew (mirrors the check in PaymentController::createOrderPayment)
            $discordRule = new DiscordServerMembership('1015093239092625410', $order->package_id);
            if (!$discordRule->passes('discord_check', true)) {
                return redirect()->back()->with('error', $discordRule->message());
            }

            // Discord trust enforcement removed for HCTestDash (onboarding challenge build).
        }


        // if due date is set, check if its 14 days ahead of the due date
        if (isset($validated['custom_date'])) {
            $customDate = Carbon::parse($validated['custom_date']);
            $minRenewalDays = $order->package->settings('min_custom_renewal_days', 14);

            if (!$order->package->settings('allow_custom_renewal_date', true)) {
                return redirect()->back()->with('error', 'This package does not allow custom renewal dates');
            }

            if ($customDate->timestamp <= $order->due_date->addDays($minRenewalDays - 1)->timestamp) {
                return redirect()->back()->with('error', "The custom date must be at least {$minRenewalDays} days ahead of the due date");
            }
        }

        // define the days to add
        $periodInDays = isset($validated['days']) ? $validated['days'] : $order->due_date->diffInDays($customDate) + 1;

        // make sure period in days is atleast 1 or more
        if ($periodInDays < 1) {
            return redirect()->back()->with('error', 'The period in days must be at least 1');
        }

        // check if there isn't any duplicate payment
        $duplicate_payment = $order->payments()->whereStatus('unpaid')->where('due_date', $order->due_date);
        if ($duplicate_payment->exists()) {
            $duplicate_payment->first()->delete();
        }

        // calculate price
        $price = $order->price()->renewal_price / $order->price()->period * $periodInDays;
        $price = number_format($price, 2);

        $payment = Payment::generate([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'description' => __('admin.ptero_renewal_desc', [
                'name' => $order->name,
                'param' => $order->due_date->translatedFormat(settings('date_format', 'd M Y')),
                'add_days_period' => $order->due_date->addDays($periodInDays)->translatedFormat(settings('date_format', 'd M Y')),
            ]),
            'amount' => $price,
            'due_date' => $order->due_date,
            'options' => ['period' => $periodInDays],
            'handler' => \App\Handlers\Renewal::class,
        ]);

        return redirect()->route('invoice', ['payment' => $payment->id])->with('success', __('admin.invoice_generated_successfully'));
    }

    /**
     * This function manages cancellations
     */
    public function cancel(Order $order)
    {
        if (auth()->id() !== $order->user_id && !auth()->user()->is_admin()) {
            abort(403);
        }

        request()->validate([
            'cancelled_at' => 'required',
            'cancel_reason' => 'max:255',
        ]);

        if (!$order->package->settings('allow_cancellation', true)) {
            return redirect()->back()->with('error', __('client.package_does_not_allow_cancellation'));
        }

        if ($order->status !== 'active') {
            return redirect()->back()->with('error', __('admin.service_already_cancelled'));
        }

        if ($order->price['cancellation_fee'] > 0)
        {
            $payment = Payment::generate([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'description' => 'Cancellation: '. $order->name,
                'amount' => $order->price['cancellation_fee'],
                'due_date' => Carbon::now()->addHours(6),
                'options' => request()->except(['_token', 'gateway']),
                'handler' => \App\Handlers\Cancel::class,
            ]);

            return redirect()->route('invoice.pay', ['payment' => $payment->id, 'gateway' => request()->input('gateway')])->with('success',
                __('admin.pay_cancellation_fee_cancel_service'));
        }

        $order->cancel(request()->input('cancelled_at'), request()->input('cancel_reason'));

        return redirect()->back()->with('success', __('admin.your_service_was_cancelled'));
    }

    public function upgrade(Order $order)
    {
        if (!$order->getService()->canUpgrade()) {
            return redirect()->back();
        }

        if ($order->hasActiveSubscription())
        {
            return redirect()->back()->withError(__('client.cancel_subscription_to_continue'));
        }

        if ($order->status !== 'active') {
            return redirect()->back()->withError(__('client.order_must_be_active'));
        }

        // check if new package allows upgrades
        if (!$order->package->settings('allow_upgrading', true)) {
            return redirect()->back()->withError('Your current package does not allow upgrading');
        }

        $upgrade = request()->validate([
            'package_id' => 'required|numeric',
            'price_id' => 'required|numeric',
            'gateway' => 'required',
        ]);

        $newPackage = Package::findOrFail($upgrade['package_id']);
        $newPrice = PackagePrice::where('package_id', $newPackage->id)->where('id', $upgrade['price_id'])->firstOrFail();

        // prevent people from upgrading to inactive packages
        if ($newPackage->status !== 'active') {
            return redirect()->back()->withError('The package you are trying to upgrade to is not active');
        }

        // check if new package allows upgrades
        if (!$newPackage->settings('allow_upgrading', true)) {
            return redirect()->back()->withError('The package you are trying to upgrade to does not allow upgrading');
        }

        // prevent cross service upgrading
        if ($order->package->service !== $newPackage->service) {
            return redirect()->back()->withError("Your current order uses {$order->package->service}, the new package has to support the same service, but uses {$newPackage->service}");
        }

        // prevent cross category upgrading
        if ($order->package->category->id !== $newPackage->category->id) {
            return redirect()->back();
        }

        // define the upgrade description
        $upgradeDescription = "Upgrade: {$newPackage->name}";

        // calculate the upgrade price
        $upgrade_price = ($newPrice['renewal_price'] / $newPrice['period'] - $order->price['renewal_price'] / $order->price['period']) * now()->diffInDays($order->due_date);
        $upgrade_price = $upgrade_price + $newPrice['upgrade_fee'];
        $upgrade_price = ($upgrade_price > 0) ? number_format($upgrade_price, 2) : 0;

        $payment = Payment::generate([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'description' => $upgradeDescription,
            'amount' => $upgrade_price,
            'options' => $upgrade,
            'handler' => \App\Handlers\Upgrade::class,
            'show_as_unpaid_invoice' => false,
        ]);

        return redirect()->route('invoice.pay', ['payment' => $payment->id, 'gateway' => request()->input('gateway')]);
    }

    public function toggleBalanceRenew(Order $order)
    {
        if(!$order->package->settings('allow_auto_balance_renewal', true) AND $order->auto_balance_renew == false) {
            return redirect()->back()->withError('This package does not allow auto balance renewal');
        }

        if ($order->user->balance < $order->price()->renewal_price and !$order->auto_balance_renew) {
            return redirect()->back()->withError(__('client.insufficient_balance_to_auto_renew', ['amount' => price($order->price()->renewal_price)]));
        }

        if (!$order->auto_balance_renew) {
            // send user an email if "auto_renew_last_enabled_at" was more than 3 days ago
            $lastEnabledAt = $order->settings('auto_renew_last_enabled_at', false);
            if (!$lastEnabledAt or $lastEnabledAt < now()->subDays(3)->timestamp) {
                $order->user->email([
                    'subject' => __('client.auto_renew_with_balance'),
                    'content' => __('client.auto_renew_with_balance_enabled_email', ['order' => $order->name . " (#$order->id)"]),
                ]);

                $order->settings()->put('auto_renew_last_enabled_at', now()->timestamp);
            }
        }

        $order->auto_balance_renew = !$order->auto_balance_renew;
        $order->save();

        return redirect()->route('service', ['order' => $order->id, 'page' => 'manage']);
    }

    /**
     * This function manages logins to the panel
     */
    public function loginToPanel(Order $order)
    {
        if (!$order->getService()->canLoginToPanel()) {
            return redirect()->back()->withError('This service does not support automatic login');
        }

        return $order->getService()->class->loginToPanel($order);
    }

    /**
     * This function handles deletion/termination of free services (FreePterodactyl)
     */
    public function deleteService(Order $order)
    {
        if (auth()->id() !== $order->user_id && !auth()->user()->is_admin()) {
            abort(403);
        }

        // Only allow deletion for FreePterodactyl services
        if (!($order->package && $order->package->service === 'freepterodactyl')) {
            return redirect()->back()->with('error', 'This action is only available for free services.');
        }

        // Prevent double termination
        if ($order->status === 'terminated') {
            return redirect()->back()->with('error', 'This service has already been terminated.');
        }

        try {
            $order->forceTerminate();
        } catch (\Exception $e) {
            $order->status = 'terminated';
            $order->save();
        }

        return redirect()->route('dashboard')->with('success', 'Your free server has been deleted successfully. You can now request a new free server.');
    }

    /**
     * This function manages restart for cancelled orders
     */
    public function undoCancel(Order $order)
    {
        if ($order->status = 'cancelled')
        {
            $order->status = 'active';
            $order->cancelled_at = null;
            $order->cancel_reason = null;
            $order->save();
        }

        return redirect()->back()->with('success', __('admin.undo_canceled_resp'));
    }

    public function getPackagePrices(Package $package)
    {
        return $package->prices()->where('is_active', 1)->get()->map(function ($price) {
            return collect($price->toArray())->only(['id', 'period', 'price', 'renewal_price', 'setup_fee', 'upgrade_fee', 'cancellation_fee'])
                ->merge(['cycle' => $price->period()])->all();
        });
    }
}
