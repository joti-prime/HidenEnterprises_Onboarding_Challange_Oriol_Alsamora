<?php

namespace App\Models;

use App\Services\Geo\CountryLookup;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * App\Models\Device
 *
 * @property int $id
 * @property int $user_id
 * @property int $is_revoked
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $device_name
 * @property string|null $device_type
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Device newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Device newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Device query()
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereIsRevoked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Device extends Model
{
    use HasFactory;

    protected $table = 'user_devices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'network_fingerprint',
        'user_agent',
        'user_agent_normalized',
        'seen_countries',
        'device_name',
        'device_type',
        'last_login_at',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'seen_countries' => 'array',
    ];

    public static function addDevice(Request $request, $user)
    {
        // Skip device tracking during impersonation
        if (session()->has('is_impersonating')) {
            return 0;
        }

        // Get the device information
        $userAgent = $request->header('User-Agent');

        if ($userAgent == null) {
            return 0;
        }

        $ip = $request->ip();
        $networkFingerprint = self::networkFingerprintFor($ip);
        $userAgentNormalized = self::normalizeUserAgent($userAgent);
        $country = app(CountryLookup::class)->country($ip);

        // Match only on normalized user-agent. The country (resolved via local
        // GeoIP DB, no external call) decides whether to alert. This stops the
        // spam for mobile users whose carrier rotates the IP across many /24s
        // but keeps the alert when the same UA appears from a country never
        // seen before for that device (e.g. credential stuffing from abroad).
        $existing = $user->devices()
            ->where('user_agent_normalized', $userAgentNormalized)
            ->first();

        if ($existing) {
            $seen = is_array($existing->seen_countries) ? $existing->seen_countries : [];
            $countryIsNew = $country !== null && ! in_array($country, $seen, true);
            if ($countryIsNew) {
                $seen[] = $country;
            }

            $existing->update([
                'last_login_at' => Carbon::now(),
                'ip_address' => $ip,
                'network_fingerprint' => $networkFingerprint,
                'user_agent' => $userAgent,
                'seen_countries' => $seen,
            ]);

            if ($countryIsNew) {
                self::sendNewDeviceAlert($user, $existing);
            }

            return 0;
        }

        $device = Device::updateOrCreate([
            'user_id' => $user->id,
            'ip_address' => $ip,
            'network_fingerprint' => $networkFingerprint,
            'user_agent' => $userAgent,
            'user_agent_normalized' => $userAgentNormalized,
            'seen_countries' => $country !== null ? [$country] : [],
            'device_name' => Device::getDeviceCategory($userAgent),
            'device_type' => Device::getDeviceType($userAgent),
            'last_login_at' => Carbon::now(),
        ]);

        if ($user->devices->count() > 1) {
            self::sendNewDeviceAlert($user, $device);
        }
    }

    protected static function sendNewDeviceAlert($user, Device $device): void
    {
        app()->setLocale($user->language);
        $user->email([
            'subject' => __('client.email_new_device_subject', ['app_name' => settings('app_name', config('app.name'))]),
            'content' => emailMessage('new_device', $user->language) . __('client.email_add_device_content', [
                'device_name' => $device->device_name,
                'device_type' => $device->device_type,
                'ip_address' => $device->ip_address,
                'last_login_at' => $device->last_login_at,
                'user_agent' => $device->user_agent,
            ]),
            'button' => [
                'name' => __('client.email_check_activity_btn'),
                'url' => route('user.settings'),
            ],
        ]);

        $user->notify([
            'type' => 'danger',
            'icon' => "<i class='bx bx-desktop' ></i>",
            'message' => __('client.email_new_device_subject', ['app_name' => settings('app_name', 'HCTestDash')]),
            'button_url' => route('user.settings'),
        ]);
    }

    protected static function getDeviceType($userAgent)
    {
        $deviceType = 'Unknown';

        // Check if the user agent contains keywords for specific device types
        if (strpos($userAgent, 'iPhone') !== false) {
            $deviceType = 'iPhone';
        } elseif (strpos($userAgent, 'iPad') !== false) {
            $deviceType = 'iPad';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $deviceType = 'Android';
        } elseif (strpos($userAgent, 'Windows Phone') !== false) {
            $deviceType = 'Windows Phone';
        } elseif (stripos($userAgent, 'Windows') !== false) {
            $deviceType = 'Windows';
        } elseif (stripos($userAgent, 'Macintosh') !== false) {
            $deviceType = 'Mac';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $deviceType = 'Linux';
        } elseif (strpos($userAgent, 'BlackBerry') !== false) {
            $deviceType = 'BlackBerry';
        }

        return $deviceType;
    }

    protected static function getDeviceCategory($userAgent)
    {
        $deviceCategory = 'Unknown';

        // Check if the user agent contains keywords for specific device categories
        if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false || strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false || strpos($userAgent, 'Windows Phone') !== false || strpos($userAgent, 'BlackBerry') !== false) {
            $deviceCategory = 'Phone';
        } elseif (strpos($userAgent, 'Windows') !== false || strpos($userAgent, 'Macintosh') !== false || strpos($userAgent, 'Linux') !== false) {
            $deviceCategory = 'Desktop';
        }

        return $deviceCategory;
    }

    public function revoke()
    {
        if ($this->is_revoked) {
            $this->is_revoked = false;
        } else {
            $this->is_revoked = true;
        }

        $this->save();
    }

    /**
     * Network fingerprint of an IP: /24 for IPv4, /32 for IPv6.
     * Returns null when the value is not a valid IP.
     *
     * IPv6 uses /32 (not /48) so mobile carriers and iCloud Private Relay,
     * which rotate the customer prefix below /32, do not look like new devices.
     */
    public static function networkFingerprintFor(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return $parts[0].'.'.$parts[1].'.'.$parts[2].'.0/24';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $packed = inet_pton($ip);
            if ($packed === false || strlen($packed) !== 16) {
                return null;
            }
            $hex = bin2hex($packed);
            $prefix = substr($hex, 0, 8);
            return preg_replace('/(.{4})(?!$)/', '$1:', $prefix).'::/32';
        }

        return null;
    }

    /**
     * Collapse a User-Agent into "Platform|Browser" so that browser/OS version
     * bumps do not register as a new device.
     */
    public static function normalizeUserAgent(?string $userAgent): string
    {
        if ($userAgent === null || $userAgent === '') {
            return 'Unknown|Unknown';
        }

        $platform = 'Other';
        if (stripos($userAgent, 'iPhone') !== false) {
            $platform = 'iPhone';
        } elseif (stripos($userAgent, 'iPad') !== false) {
            $platform = 'iPad';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $platform = 'Android';
        } elseif (stripos($userAgent, 'Windows Phone') !== false) {
            $platform = 'WindowsPhone';
        } elseif (stripos($userAgent, 'Windows') !== false) {
            $platform = 'Windows';
        } elseif (stripos($userAgent, 'Macintosh') !== false || stripos($userAgent, 'Mac OS X') !== false) {
            $platform = 'Mac';
        } elseif (stripos($userAgent, 'CrOS') !== false) {
            $platform = 'ChromeOS';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $platform = 'Linux';
        } elseif (stripos($userAgent, 'BlackBerry') !== false) {
            $platform = 'BlackBerry';
        }

        $browser = 'Other';
        if (stripos($userAgent, 'Edg/') !== false || stripos($userAgent, 'EdgA/') !== false || stripos($userAgent, 'EdgiOS/') !== false) {
            $browser = 'Edge';
        } elseif (stripos($userAgent, 'OPR/') !== false || stripos($userAgent, 'Opera') !== false) {
            $browser = 'Opera';
        } elseif (stripos($userAgent, 'Firefox/') !== false || stripos($userAgent, 'FxiOS/') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'Chrome/') !== false || stripos($userAgent, 'CriOS/') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (stripos($userAgent, 'curl/') !== false) {
            $browser = 'curl';
        } elseif (stripos($userAgent, 'wget/') !== false) {
            $browser = 'wget';
        }

        return $platform.'|'.$browser;
    }
}
