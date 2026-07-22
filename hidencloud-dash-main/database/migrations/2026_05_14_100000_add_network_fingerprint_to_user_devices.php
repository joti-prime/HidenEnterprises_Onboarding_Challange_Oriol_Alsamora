<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->string('network_fingerprint', 64)->nullable()->after('ip_address');
        });

        // Backfill: derive /24 (IPv4) or /48 (IPv6) from existing ip_address.
        DB::table('user_devices')->whereNotNull('ip_address')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                $fp = \App\Models\Device::networkFingerprintFor($row->ip_address);
                if ($fp !== null) {
                    DB::table('user_devices')->where('id', $row->id)->update(['network_fingerprint' => $fp]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropColumn('network_fingerprint');
        });
    }
};
