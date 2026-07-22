<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->boolean('is_recurring')->default(false);
            $table->enum('discount_type', ['flat', 'percentage']);
            $table->float('discount_amount')->default(0);
            $table->string('currency')->default('USD');
            $table->integer('allowed_uses')->default(1);
            $table->enum('coupon_type', ['checkout', 'product'])->default('checkout');
            $table->json('applicable_products')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
