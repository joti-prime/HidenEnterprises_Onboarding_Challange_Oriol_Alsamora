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
        Schema::create('package_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();

            // Foreign key constraint to ensure config options are deleted when a package is deleted
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');

            // Unique constraint to ensure only one config option per package
            $table->unique(['package_id', 'key'], 'package_settings_package_id_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_settings');
    }
};
