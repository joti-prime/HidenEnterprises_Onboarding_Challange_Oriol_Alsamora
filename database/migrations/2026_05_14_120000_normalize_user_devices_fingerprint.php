<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->string('user_agent_normalized', 64)->nullable()->after('user_agent');
        });

        // Backfill user_agent_normalized in a single statement. The CASE order
        // mirrors Device::normalizeUserAgent so future rows match historic ones.
        DB::statement("
            UPDATE user_devices
            SET user_agent_normalized = CONCAT(
                CASE
                    WHEN user_agent LIKE '%iPhone%' THEN 'iPhone'
                    WHEN user_agent LIKE '%iPad%' THEN 'iPad'
                    WHEN user_agent LIKE '%Android%' THEN 'Android'
                    WHEN user_agent LIKE '%Windows Phone%' THEN 'WindowsPhone'
                    WHEN user_agent LIKE '%Windows%' THEN 'Windows'
                    WHEN user_agent LIKE '%Macintosh%' OR user_agent LIKE '%Mac OS X%' THEN 'Mac'
                    WHEN user_agent LIKE '%CrOS%' THEN 'ChromeOS'
                    WHEN user_agent LIKE '%Linux%' THEN 'Linux'
                    WHEN user_agent LIKE '%BlackBerry%' THEN 'BlackBerry'
                    ELSE 'Other'
                END,
                '|',
                CASE
                    WHEN user_agent LIKE '%Edg/%' OR user_agent LIKE '%EdgA/%' OR user_agent LIKE '%EdgiOS/%' THEN 'Edge'
                    WHEN user_agent LIKE '%OPR/%' OR user_agent LIKE '%Opera%' THEN 'Opera'
                    WHEN user_agent LIKE '%Firefox/%' OR user_agent LIKE '%FxiOS/%' THEN 'Firefox'
                    WHEN user_agent LIKE '%Chrome/%' OR user_agent LIKE '%CriOS/%' THEN 'Chrome'
                    WHEN user_agent LIKE '%Safari%' THEN 'Safari'
                    WHEN user_agent LIKE '%curl/%' THEN 'curl'
                    WHEN user_agent LIKE '%wget/%' THEN 'wget'
                    ELSE 'Other'
                END
            )
            WHERE user_agent IS NOT NULL
        ");

        DB::statement("
            UPDATE user_devices
            SET user_agent_normalized = 'Unknown|Unknown'
            WHERE user_agent IS NULL
        ");

        // Recompute network_fingerprint from /48 to /32 for IPv6 in one shot.
        // IPv4 /24 rows already match the new scheme so we leave them alone.
        DB::statement("
            UPDATE user_devices
            SET network_fingerprint = CONCAT(SUBSTRING_INDEX(network_fingerprint, ':', 2), '::/32')
            WHERE network_fingerprint LIKE '%::/48'
        ");

        // Dedupe: per user, keep the most recent row per
        // (user_agent_normalized, network_fingerprint). NULL-safe via <=>.
        DB::statement('
            DELETE d1 FROM user_devices d1
            INNER JOIN user_devices d2
                ON d1.user_id = d2.user_id
                AND d1.user_agent_normalized <=> d2.user_agent_normalized
                AND d1.network_fingerprint <=> d2.network_fingerprint
                AND d1.id < d2.id
        ');

        Schema::table('user_devices', function (Blueprint $table) {
            $table->index(
                ['user_id', 'user_agent_normalized', 'network_fingerprint'],
                'user_devices_user_uan_fp_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropIndex('user_devices_user_uan_fp_idx');
            $table->dropColumn('user_agent_normalized');
        });
    }
};
