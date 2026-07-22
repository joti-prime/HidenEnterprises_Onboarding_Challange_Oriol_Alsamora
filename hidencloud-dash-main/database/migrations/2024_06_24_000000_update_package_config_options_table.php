<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('package_config_options', function (Blueprint $table) {
            $table->text('rules')->default('required')->after('type');
            $table->integer('order')->default(0)->after('data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_config_options', function (Blueprint $table) {
            $table->dropColumn('rules');
            $table->dropColumn('order');
        });
    }
};
