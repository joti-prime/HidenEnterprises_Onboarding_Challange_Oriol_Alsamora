<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add the UNIQUE first so the user_id foreign key still has an index
        // covering it before we drop the old one. MariaDB refuses to drop an
        // index that is the only one covering an FK column.
        Schema::table('user_devices', function (Blueprint $table) {
            $table->unique(['user_id', 'user_agent_normalized'], 'user_devices_user_uan_unique');
        });
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropIndex('user_devices_user_uan_fp_idx');
        });
    }

    public function down(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->index(
                ['user_id', 'user_agent_normalized', 'network_fingerprint'],
                'user_devices_user_uan_fp_idx'
            );
        });
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropUnique('user_devices_user_uan_unique');
        });
    }
};
