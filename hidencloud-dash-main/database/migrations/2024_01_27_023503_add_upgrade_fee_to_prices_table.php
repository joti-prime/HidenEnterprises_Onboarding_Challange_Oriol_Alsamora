<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('package_prices', function (Blueprint $table) {
            $table->after('cancellation_fee', function ($table) {
                $table->float('upgrade_fee')->default(0);
            });
        });
    }

    public function down(): void
    {
        Schema::table('package_prices', function (Blueprint $table) {
            $table->dropColumn('upgrade_fee');
        });
    }
};
