<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('wisp_order_domains');
    }

    public function down(): void
    {
        Schema::create('wisp_order_domains', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->unsigned();
            $table->json('domain_data')->nullable();
            $table->timestamps();
        });
    }
};
