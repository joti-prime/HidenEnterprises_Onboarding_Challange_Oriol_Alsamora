<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::query()->latest()->paginate(25);

        return Theme::view('coupons.index', compact('coupons'));
    }

    public function create()
    {
        return Theme::view('coupons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'is_recurring' => 'boolean',
            'discount_type' => 'required',
            'discount_amount' => 'required|numeric',
            'allowed_uses' => 'required|numeric',
            'applicable_products' => 'required',
            'notes' => 'nullable',
            'expires_at' => 'nullable|date',
        ]);

        $coupon = new Coupon;
        $coupon->code = $request->input('code');
        $coupon->is_recurring = $request->input('is_recurring', false);
        $coupon->discount_type = $request->input('discount_type', 'percentage');
        $coupon->discount_amount = $request->input('discount_amount');
        $coupon->allowed_uses = $request->input('allowed_uses');
        $coupon->applicable_products = $request->input('applicable_products', []);
        $coupon->notes = $request->input('notes');
        $coupon->expires_at = $request->input('expires_at');
        $coupon->save();

        return redirect()->route('coupons.index')->with('success',
            trans('responses.create_coupon_success', ['name' => $coupon->code, 'default' => 'The :name created successfully.']));
    }

    public function edit(Coupon $coupon)
    {
        return Theme::view('coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required',
            'is_recurring' => 'boolean',
            'discount_type' => 'required',
            'discount_amount' => 'required|numeric',
            'allowed_uses' => 'required|numeric',
            'applicable_products' => 'required',
            'notes' => 'nullable',
            'expires_at' => 'nullable|date',
        ]);

        $coupon->code = $request->input('code');
        $coupon->is_recurring = $request->input('is_recurring', false);
        $coupon->discount_type = $request->input('discount_type', 'percentage');
        $coupon->discount_amount = $request->input('discount_amount');
        $coupon->allowed_uses = $request->input('allowed_uses');
        $coupon->applicable_products = $request->input('applicable_products', []);
        $coupon->notes = $request->input('notes');
        $coupon->expires_at = $request->input('expires_at');
        $coupon->save();

        return redirect()->back()->with('success',
            trans('responses.update_coupon_success', ['name' => $coupon->code, 'default' => 'The :name update successfully.']));
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('coupons.index')->with('success',
            trans('responses.delete_coupon_success', ['name' => $coupon->code, 'default' => 'The :name delete successfully.']));
    }
}
