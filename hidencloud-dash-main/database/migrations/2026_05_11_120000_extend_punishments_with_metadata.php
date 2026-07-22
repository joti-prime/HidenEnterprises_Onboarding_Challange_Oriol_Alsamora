<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('punishments', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('expires_at');
            $table->string('source', 50)->default('manual')->after('metadata');
            $table->string('external_reference', 100)->nullable()->after('source');

            $table->index(['source', 'created_at']);
            $table->index('external_reference');
        });

        DB::statement('ALTER TABLE punishments MODIFY reason TEXT NULL');
    }

    public function down(): void
    {
        Schema::table('punishments', function (Blueprint $table) {
            $table->dropIndex(['source', 'created_at']);
            $table->dropIndex(['external_reference']);
            $table->dropColumn(['metadata', 'source', 'external_reference']);
        });

        DB::statement('ALTER TABLE punishments MODIFY reason VARCHAR(255) NULL');
    }
};
