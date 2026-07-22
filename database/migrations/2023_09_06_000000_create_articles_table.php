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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('path')->unique();
            $table->string('title');
            $table->text('content')->nullable();
            $table->enum('status', ['draft', 'published', 'unlisted'])->default('draft');
            $table->json('keywords')->nullable();
            $table->json('labels')->nullable();
            $table->integer('views')->default(0);
            $table->integer('likes')->default(0);
            $table->integer('dislikes')->default(0);
            $table->boolean('show_author')->default(true);
            $table->boolean('allow_comments')->default(true);
            $table->boolean('allow_guests')->default(true);
            $table->timestamps();

            // Foreign key constraint to ensure orders are deleted when a user is deleted
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
};
