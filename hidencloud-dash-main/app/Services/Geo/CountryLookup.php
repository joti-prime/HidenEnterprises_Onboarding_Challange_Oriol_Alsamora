<?php

namespace App\Services\Geo;

use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Log;
use Throwable;

class CountryLookup
{
    protected ?Reader $reader = null;

    protected bool $readerAttempted = false;

    protected array $cache = [];

    public function country(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }

        if (array_key_exists($ip, $this->cache)) {
            return $this->cache[$ip];
        }

        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->cache[$ip] = null;
        }

        // Private, reserved and loopback addresses do not have a country.
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $this->cache[$ip] = null;
        }

        $reader = $this->reader();
        if ($reader === null) {
            return $this->cache[$ip] = null;
        }

        try {
            $record = $reader->country($ip);
            $iso = $record->country->isoCode;
            return $this->cache[$ip] = ($iso !== null && $iso !== '') ? strtoupper($iso) : null;
        } catch (Throwable) {
            // AddressNotFoundException is expected for some valid public IPs.
            return $this->cache[$ip] = null;
        }
    }

    protected function reader(): ?Reader
    {
        if ($this->reader !== null) {
            return $this->reader;
        }
        if ($this->readerAttempted) {
            return null;
        }
        $this->readerAttempted = true;

        $path = storage_path('app/geoip/Country.mmdb');
        if (! is_file($path) || ! is_readable($path)) {
            Log::warning('GeoIP database missing or unreadable', ['path' => $path]);
            return null;
        }

        try {
            return $this->reader = new Reader($path);
        } catch (Throwable $e) {
            Log::warning('GeoIP database failed to open', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
