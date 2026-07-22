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
            $table->string('identifier')->nullable()->after('user_id');
            $table->boolean('show')->default(true)->after('attachment');
            $table->boolean('has_footer')->default(true)->after('is_sent');
            $table->boolean('seen')->default(false)->after('is_sent');
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
            $table->dropColumn('identifier');
            $table->dropColumn('show');
            $table->dropColumn('has_footer');
            $table->dropColumn('seen');
        });
    }
};
