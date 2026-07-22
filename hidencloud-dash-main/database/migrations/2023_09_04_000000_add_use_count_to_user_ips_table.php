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
        Schema::table('user_ips', function (Blueprint $table) {
            $table->integer('uses')->default(1)->after('ip_address'); // add your column type and name
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_ips', function (Blueprint $table) {
            $table->integer('uses'); // specify the column to be dropped
        });
    }
};
