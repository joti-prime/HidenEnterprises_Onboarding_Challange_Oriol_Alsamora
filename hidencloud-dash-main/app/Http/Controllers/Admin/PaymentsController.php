<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Gateways\Gateway;
use App\Models\Payment;
use App\Models\Order;
use App\Models\ErrorLog;

class PaymentsController extends Controller
{
    public function index($status)
    {
        $payments = Payment::query();

        if (request()->get('sort') == 'latest' or request()->get('sort') == null) {
            $payments->latest();
        }

        if (request()->get('sort') == 'random') {
            $payments->inRandomOrder();
        }

        if (request()->get('sort') == 'oldest') {
            $payments->oldest();
        }

        if (isset(request()->filter)) {
            foreach (request()->filter as $filter) {
                if (in_array($filter['operator'], ['LIKE', 'NOT LIKE'])) {
                    $filter['value'] = '%' . $filter['value'] . '%';
                }

                $payments->where($filter['key'], $filter['operator'], $filter['value']);
            }
        }

        $payments = $payments->whereNot('type', 'subscription')->whereStatus($status)->paginate(request()->get('per_page', 20));

        return Theme::view('payments.index', compact('payments', 'status'));
    }

    public function subscriptions($status)
    {
        $payments = Payment::whereType('subscription')->whereStatus($status)->latest()->paginate(15);

        return Theme::view('payments.subscriptions', compact('payments', 'status'));
    }

    public function create()
    {
        return Theme::view('payments.create');
    }

    public function edit(Payment $payment)
    {
        return Theme::view('payments.edit', compact('payment'));
    }

    public function store()
    {
        $validatedData = request()->validate([
            'description' => 'required',
            'amount' => 'required|numeric|between:1,999',
            'status' => 'required|string',
            'user_id' => 'required|numeric',
            'due_date' => 'date',
            'notes' => 'max:1000',
            'coupon' => 'max:128',
        ]);

        $payment = Payment::generate([
            'user_id' => $validatedData['user_id'],
            'type' => 'once',
            'description' => $validatedData['description'],
            'amount' => $validatedData['amount'],
            'status' => $validatedData['status'],
            'due_date' => $validatedData['due_date'],
            'notes' => $validatedData['notes'],
        ]);

        return redirect(route('payments.edit', ['payment' => $payment->id]))->with('success',
            trans('responses.payment_create_success',
                ['default' => 'Payment was created successfully!'])
        );
    }

    public function update(Payment $payment)
    {
        $validatedData = request()->validate([
            'description' => 'required',
            'amount' => 'required|numeric|between:1,999',
            'user_id' => 'required|numeric',
            'due_date' => 'date',
            'notes' => 'max:1000',
        ]);

        $payment->description = $validatedData['description'];
        $payment->amount = $validatedData['amount'];
        $payment->transaction_id = request()->input('transaction_id');
        // $payment->coupon = request()->input('coupon', $payment->coupon); // todo
        $payment->user_id = $validatedData['user_id'];
        $payment->due_date = request()->input('due_date', $payment->due_date);
        $payment->notes = $validatedData['notes'];
        $payment->save();

        return redirect()->back()->with('success',
            trans('responses.payment_update_success',
                ['default' => 'Payment has been updated!'])
        );
    }

    public function refund(Payment $payment)
    {
        request()->validate([
            'amount' => 'required|numeric|between:0.01,' . $payment->amount,
        ]);

        if (!isset($payment->gateway['refund_support']) or !$payment->gateway['refund_support']) {
            return redirect()->back()->with('success',
                trans('responses.payment_refund_support',
                    ['default' => 'This gateway does not support refunds.'])
            );
        }

        $payment->refunded(request()->input('amount'));

        // Terminate the order if checkbox is checked
        if (request()->input('terminate_order') && $payment->order_id) {
            $order = Order::find($payment->order_id);
            if ($order) {
                try {
                    $order->terminate();
                } catch (\Exception $e) {
                    // Log the error but continue with the refund
                    ErrorLog('refund::terminate_order', 'Failed to terminate order ' . $order->id . ': ' . $e->getMessage());
                }
            }
        }

        return redirect()->back()->with('success',
            trans('responses.payment_refund_success',
                ['default' => 'This payment has been refunded to the user'])
        );

    }

    public function complete(Payment $payment)
    {
        request()->validate([
            'gateway' => 'required|numeric',
        ]);

        $gateway = Gateway::findOrFail(request()->input('gateway'));
        $payment->gateway = $gateway->toArray();
        $payment->save();
        $payment->completed(request()->input('transaction_id', null));

        return redirect()->back()->with('success',
            trans('responses.payment_completed_success',
                ['default' => 'This payment has been completed'])
        );
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->back()->with('success', 'Payment has been deleted');
    }

    public function search()
    {
        $query = request()->query('query');

        return Payment::query()->where('id', 'LIKE', "%$query%")->take(5)->get();
    }
}
