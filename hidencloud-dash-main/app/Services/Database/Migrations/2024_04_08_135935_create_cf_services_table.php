<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cf_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id');
            $table->string('type')->default('minecraft');
            $table->json('zones_ids');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cf_services');
    }
};
