<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('sender');
            $table->string('receiver');
            $table->string('subject');
            $table->text('content');
            $table->json('button')->nullable();
            $table->json('attachment')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->integer('resent')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();

            // Foreign key constraint to ensure IPs are deleted when a user is deleted
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_histories');
    }
}
