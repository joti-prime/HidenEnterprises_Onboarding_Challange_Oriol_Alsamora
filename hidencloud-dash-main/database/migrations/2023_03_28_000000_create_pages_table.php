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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('path')->unique();
            $table->string('title')->nullable();
            $table->string('icon')->nullable();
            $table->text('content')->nullable();
            $table->string('redirect_url')->nullable();
            $table->boolean('new_tab')->default(0);
            $table->boolean('is_enabled')->default(1);
            $table->boolean('basic_page')->default(0);
            $table->json('placement')->nullable();
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
        Schema::dropIfExists('pages');
    }
};
