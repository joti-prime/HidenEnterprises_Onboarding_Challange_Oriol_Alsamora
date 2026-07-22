<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mass_mails', function (Blueprint $table) {
            $table->id();
            $table->string('audience')->default('all_users');
            $table->string('subject');
            $table->text('content');
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('attachment')->nullable();
            $table->string('email_theme')->default('default');
            $table->string('status')->default('pending');
            $table->integer('repeat')->nullable();
            $table->integer('sent_count')->default(0);
            $table->json('custom_selection')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('last_completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mass_mails');
    }
};
