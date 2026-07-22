<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('status_monitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // For type=http this is a full URL, for type=tcp this is a hostname/IP
            $table->string('target');
            // http = real HTTP request, tcp = raw TCP connection test (reachability check beyond HTTP)
            $table->enum('check_type', ['http', 'tcp'])->default('http');
            // Only used for check_type = tcp
            $table->unsignedInteger('port')->nullable();
            // Only used for check_type = http, defaults to 200
            $table->unsignedSmallInteger('expected_status_code')->default(200);
            $table->boolean('is_enabled')->default(true);

            // Last known result, refreshed by the scheduler and "Check now"
            $table->enum('last_status', ['up', 'down', 'unknown'])->default('unknown');
            $table->unsignedInteger('last_response_time_ms')->nullable();
            $table->unsignedSmallInteger('last_status_code')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('last_checked_at')->nullable();

            $table->timestamps();
        });

        // Enable the dashboard widget by default so the status card shows up
        // for users without requiring a manual settings change.
        if (Schema::hasTable('settings')) {
            \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
                ['key' => 'widget:dashboard:statusmonitor'],
                ['value' => '1']
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_monitors');
    }
};
