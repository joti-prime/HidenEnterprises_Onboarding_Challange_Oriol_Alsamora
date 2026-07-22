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
        Schema::create('package_config_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->string('key');
            $table->string('type');
            $table->integer('price_per_30_days')->default(0);
            $table->boolean('is_onetime')->default(false);
            $table->boolean('is_required')->default(false);
            $table->string('icon')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            // Foreign key constraint to ensure config options are deleted when a package is deleted
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_config_options');
    }
};
