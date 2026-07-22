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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('price_id')->nullable();
            $table->string('type')->nullable();
            $table->string('description')->nullable();
            $table->string('status')->default('unpaid');
            $table->string('currency')->default('USD');
            $table->float('amount');
            $table->string('transaction_id')->nullable();
            $table->string('handler')->nullable();
            $table->json('gateway')->nullable();
            $table->json('data')->nullable();
            $table->json('options')->nullable();
            $table->boolean('show_as_unpaid_invoice')->default(true);
            $table->text('notes')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamps();

            // Foreign key constraint to ensure orders are deleted when a user is deleted
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Add a nullable foreign key constraint for order_id
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
