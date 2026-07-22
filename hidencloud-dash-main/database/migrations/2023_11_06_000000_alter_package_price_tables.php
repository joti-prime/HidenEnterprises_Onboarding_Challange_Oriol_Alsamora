<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('package_prices', function (Blueprint $table) {
            if (!Schema::hasColumn('package_prices', 'type')) {
                $table->enum('type', ['single', 'recurring'])->default('recurring')->after('package_id');
            }
        });
    }

    public function down()
    {

        Schema::table('package_prices', function (Blueprint $table) {
            if (Schema::hasColumn('package_prices', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
