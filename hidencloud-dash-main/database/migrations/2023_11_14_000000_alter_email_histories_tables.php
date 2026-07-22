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
        Schema::table('email_histories', function (Blueprint $table) {
            // Change the 'user_id' column to be nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_histories', function (Blueprint $table) {
            // Revert the 'user_id' column to be not nullable
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
