<?php

namespace App\Http\Controllers\Client;

use App\Facades\Theme;
use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Coupon;
use App\Models\Package;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

class StoreController extends Controller
{
    public function index(Categories $categories)
    {
        return Theme::view('store.index', compact('categories'));
    }

    public function pricing(Package $package)
    {
        return Theme::view('store.pricing', compact('package'));
    }

    public function service($service)
    {
        $category = Categories::query()->where('link', $service)->first();
        if (!$category) {
            $aliasMatch = Categories::query()
                ->whereNotNull('aliases')
                ->whereJsonContains('aliases', $service)
                ->first();
            if ($aliasMatch) {
                return redirect()->route('store.service', ['service' => $aliasMatch->link], 301);
            }
            abort(404);
        }
        if (in_array($category->status, ['inactive', 'restricted', 'admin_unlisted'], true)) {
            if (!auth()->check() or !auth()->user()->is_admin()) {
                return redirect('/dashboard')->with('error',
                    trans('responses.package_not_available',
                        ['default' => 'This package is not available at this time.'])
                );
            }
        }

        if ($category->children()->exists()) {
            return Theme::view('store.group', compact('category'));
        }

        $ownPackageSlugs = ['vps', 'free-hosting'];
        $ownGroupLinks   = ['group-free'];
        $usesOwnPackages = in_array($category->link, $ownPackageSlugs, true)
            || ($category->parent && in_array($category->parent->link, $ownGroupLinks, true));

        if ($usesOwnPackages) {
            $packages = $category->packages;
        } else {
            $fallback = Categories::query()
                ->where('link', 'game-servers')
                ->orWhereJsonContains('aliases', 'servers')
                ->first();
            $packages = $fallback ? $fallback->packages : collect();
        }

        return Theme::view('store.store', compact('category', 'packages'));
    }

    public function viewPackage(Package $package)
    {
        $package->service()->eventLoadPackage($package);
        if ($package->status == 'inactive' or $package->status == 'restricted') {
            if (!auth()->check() or !auth()->user()->is_admin()) {
                return redirect('/dashboard')->with('error',
                    trans('responses.package_not_available',
                        ['default' => 'This package is not available at this time.'])
                );
            }
        }

        // define package in request
        request()->package = $package;

        return Theme::view('store.packages.view', compact('package'));
    }

    public function validateCoupon(Package $package, $code = ''): array
    {
        if (!Coupon::query()->where('code', $code)->exists()) {
            Session::forget('coupon_code');

            return ['success' => false, 'description' => trans('responses.coupon_invalid_expired',
                ['default' => 'Invalid coupon or coupon has expired']),
            ];
        }

        $coupon = Coupon::query()->where('code', $code)->firstOrFail();
        if (!$coupon->isValid()) {
            Session::forget('coupon_code');

            return ['success' => false, 'description' => 'Coupon has reached usage limit or has expired.'];
        }

        if (!in_array($package->id, $coupon->applicable_products)) {
            Session::forget('coupon_code');

            return ['success' => false, 'description' => trans('responses.coupon_package_applicable_error',
                ['default' => 'Coupon is not applicable for this package.']),
            ];
        }

        $coupon = $coupon->toArray();
        $coupon = Arr::except($coupon, ['id', 'allowed_uses', 'applicable_products', 'notes', 'expires_at', 'created_at', 'updated_at']);
        $discount = ($coupon['discount_type'] == 'percentage') ? $coupon['discount_amount'] . '%' : currency('symbol') . number_format($coupon['discount_amount'], 2);

        Session::put('coupon_code', $code);

        return array_merge(['success' => true, 'description' => 'Coupon applied successfully, you receive a discount of '. $discount], $coupon);

    }
}
