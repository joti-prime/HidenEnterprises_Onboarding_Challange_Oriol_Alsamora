<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ResourceApiClient;
use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function general()
    {
        return Theme::view('settings.general');
    }

    public function config()
    {
        return Theme::view('settings.config');
    }

    public function seo()
    {
        return Theme::view('settings.seo');
    }

    public function taxes()
    {
        return Theme::view('settings.taxes');
    }

    public function registrations()
    {
        return Theme::view('settings.registrations');
    }

    public function maintenance()
    {
        return Theme::view('settings.maintenance');
    }

    public function portal()
    {
        $api = new ResourceApiClient;
        $marketplace = $api->getAllResources('Templates', 'portal');
        if (array_key_exists('error', $marketplace)) {
            $marketplace = [];
        }

        return Theme::view('settings.portal', compact('marketplace'));
    }

    public function theme()
    {
        return Theme::view('settings.theme');
    }

    public function oauth()
    {
        return Theme::view('settings.oauth');
    }

    public function captcha()
    {
        return Theme::view('settings.captcha');
    }

    public function store(Request $request)
    {
        Settings::store($request);

        return redirect()->back()->with('success',
            trans('responses.settings_store_success',
                ['default' => 'settings have been stored'])
        );
    }
}
