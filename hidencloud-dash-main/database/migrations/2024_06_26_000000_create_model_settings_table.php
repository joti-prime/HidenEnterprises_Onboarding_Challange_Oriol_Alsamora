<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('model_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
            $table->morphs('metable');
            $table->timestamps();

            $table->unique(['key', 'metable_id', 'metable_type'], 'meta_unique_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_settings');
    }
};
