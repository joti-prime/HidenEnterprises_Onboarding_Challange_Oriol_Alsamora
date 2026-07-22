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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->integer('order')->unsigned()->default(0);
            $table->foreignId('category_id');
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->default('default.png');
            $table->string('service')->nullable();
            $table->string('status');
            $table->integer('global_quantity')->default(-1);
            $table->integer('client_quantity')->default(-1);
            $table->boolean('require_domain')->default(false);
            $table->boolean('allow_coupons')->default(true);
            $table->boolean('allow_notes')->default(true);
            $table->json('data')->nullable();
            $table->string('setup_on')->default('payment_received');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages');
    }
};
