<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('whatsapp_verification_tokens', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts')->default(0)->after('used_at');
        });
    }

    public function down()
    {
        Schema::table('whatsapp_verification_tokens', function (Blueprint $table) {
            $table->dropColumn('attempts');
        });
    }
};
