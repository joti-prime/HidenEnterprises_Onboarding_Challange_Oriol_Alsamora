<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE categories MODIFY status ENUM('active','unlisted','restricted','admin_unlisted','inactive') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::table('categories')->where('status', 'admin_unlisted')->update(['status' => 'restricted']);
        DB::statement("ALTER TABLE categories MODIFY status ENUM('active','unlisted','restricted','inactive') NOT NULL DEFAULT 'active'");
    }
};
