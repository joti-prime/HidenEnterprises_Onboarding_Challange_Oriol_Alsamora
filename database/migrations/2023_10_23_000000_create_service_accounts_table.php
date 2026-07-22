<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('service_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('service');
            $table->string('external_id')->nullable();
            $table->string('username')->nullable();
            $table->string('password');
            $table->json('data')->nullable();
            $table->timestamps();

            // Foreign key constraint to ensure service accounts are deleted when a user is deleted
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Foreign key constraint to ensure service accounts are deleted when a user is deleted
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_accounts');
    }
};
