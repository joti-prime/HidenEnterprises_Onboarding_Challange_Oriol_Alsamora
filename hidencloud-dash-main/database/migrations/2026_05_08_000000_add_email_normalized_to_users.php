<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_normalized')->nullable()->after('email');
            $table->index('email_normalized', 'users_email_normalized_index');
        });

        // Backfill existing rows. Done in chunks via direct UPDATE to avoid touching every model save event.
        User::query()
            ->whereNotNull('email')
            ->whereNull('email_normalized')
            ->select(['id', 'email'])
            ->orderBy('id')
            ->chunk(2000, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['email_normalized' => User::normalizeEmail($user->email)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_normalized_index');
            $table->dropColumn('email_normalized');
        });
    }
};
