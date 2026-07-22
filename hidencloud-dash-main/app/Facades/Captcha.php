<?php

namespace App\Facades;

use App\Models\Settings;
use Illuminate\Validation\Rule;

class Captcha
{
    /**
     * This function dynamically sets the values for config/services.php
     * so that oauth services can retrieve settings set by the user in the admin area
     *
     * @return void
     */
    public static function setConfig()
    {
        config([
            'services.turnstile.key' => Settings::getJson('encrypted::captcha::cloudflare', 'site_key'),
        ]);

        config([
            'services.turnstile.secret' => Settings::getJson('encrypted::captcha::cloudflare', 'secret_key'),
        ]);
    }

    /**
     * Check whether captcha is required for a specific page
     */
    public static function CloudFlareRules(string $page): array
    {
        if (!Settings::getJson('encrypted::captcha::cloudflare', 'is_enabled', false)) {
            return ['nullable'];
        }

        if (Settings::getJson('encrypted::captcha::cloudflare', $page, false)) {
            return ['required', Rule::turnstile()];
        }

        return ['sometimes', Rule::turnstile()];
    }
}
