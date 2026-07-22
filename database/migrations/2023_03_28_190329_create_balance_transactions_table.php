<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->uuid('payment_id')->nullable();
            $table->enum('result', ['+', '-', '=']);
            $table->string('description')->nullable();
            $table->float('amount');
            $table->float('balance_before_transaction');
            $table->string('currency')->default('USD');
            $table->timestamps();

            // Foreign key constraint to ensure orders are deleted when a user is deleted
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Foreign key constraint to set payment_id to null when a payment is deleted
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('balance_transactions');
    }
}
