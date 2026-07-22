<?php

namespace App\Http\Controllers\Client;

use App\Facades\Portal;
use App\Http\Controllers\Controller;
use App\Models\Categories;

class PortalController extends Controller
{
    public function index(Categories $categories)
    {
        if (settings('portal::is_redirect', false)) {
            return redirect(settings('portal::redirect_url', '/dashboard'));
        }

        if (Categories::whereLink(request()->input('category', settings('portal::default_category')))->exists()) {
            $selected_category = Categories::whereLink(request()->input('category',
                settings('portal::default_category', Categories::first()->link)))->first();
        } else {
            $selected_category = null;
        }

        return Portal::view('main', compact('selected_category', 'categories'));
    }
}
