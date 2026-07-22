<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_oauths', function (Blueprint $table) {
            $table->unique(['user_id', 'driver'], 'user_oauths_user_id_driver_unique');
            $table->index('email', 'user_oauths_email_index');
        });
    }

    public function down(): void
    {
        Schema::table('user_oauths', function (Blueprint $table) {
            $table->dropUnique('user_oauths_user_id_driver_unique');
            $table->dropIndex('user_oauths_email_index');
        });
    }
};
