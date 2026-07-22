<?php

use App\Services\Geo\CountryLookup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->json('seen_countries')->nullable()->after('user_agent_normalized');
        });

        // Backfill seen_countries from existing device IPs, but only when a GeoIP
        // database is available. On a fresh install with no GeoIP license configured
        // this step is skipped and the column simply stays null (HCTestDash build).
        if (is_file(storage_path('app/geoip/Country.mmdb'))) {
            $lookup = app(CountryLookup::class);
            $cache = [];

            DB::table('user_devices')
                ->select('ip_address')
                ->whereNotNull('ip_address')
                ->distinct()
                ->orderBy('ip_address')
                ->chunk(2000, function ($rows) use (&$cache, $lookup) {
                    foreach ($rows as $row) {
                        $cache[$row->ip_address] = $lookup->country($row->ip_address);
                    }
                });

            // Bucket IPs by resulting country so each country writes its rows in a
            // few WHERE IN updates instead of one query per IP.
            $buckets = [];
            foreach ($cache as $ip => $country) {
                $buckets[$country ?? ''][] = $ip;
            }
            foreach ($buckets as $country => $ips) {
                $json = $country === '' ? json_encode([]) : json_encode([$country]);
                foreach (array_chunk($ips, 1000) as $chunk) {
                    DB::table('user_devices')
                        ->whereIn('ip_address', $chunk)
                        ->update(['seen_countries' => $json]);
                }
            }
        }

        // Dedupe by (user_id, user_agent_normalized). Keep the row with the
        // most recent last_login_at and merge seen_countries from siblings.
        $groups = DB::select('
            SELECT user_id, user_agent_normalized
            FROM user_devices
            WHERE user_agent_normalized IS NOT NULL
            GROUP BY user_id, user_agent_normalized
            HAVING COUNT(*) > 1
        ');

        foreach ($groups as $g) {
            $rows = DB::table('user_devices')
                ->where('user_id', $g->user_id)
                ->where('user_agent_normalized', $g->user_agent_normalized)
                ->orderByDesc('last_login_at')
                ->orderByDesc('id')
                ->get();

            $keep = $rows->first();
            $countries = [];
            foreach ($rows as $row) {
                $sc = $row->seen_countries ? json_decode($row->seen_countries, true) : null;
                if (! is_array($sc)) {
                    continue;
                }
                foreach ($sc as $c) {
                    if (is_string($c) && $c !== '' && ! in_array($c, $countries, true)) {
                        $countries[] = $c;
                    }
                }
            }

            DB::table('user_devices')
                ->where('id', $keep->id)
                ->update(['seen_countries' => json_encode($countries)]);

            DB::table('user_devices')
                ->where('user_id', $g->user_id)
                ->where('user_agent_normalized', $g->user_agent_normalized)
                ->where('id', '!=', $keep->id)
                ->delete();
        }
    }

    public function down(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropColumn('seen_countries');
        });
    }
};
