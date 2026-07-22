<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'aliases')) {
                $table->json('aliases')->nullable()->after('link');
            }
        });

        $groups = DB::table('categories')->whereNull('parent_id')->get(['id', 'link']);
        $groupById = [];
        foreach ($groups as $g) {
            $groupById[$g->id] = $g->link;
        }

        $prefixByGroup = [
            'group-vps'         => null,
            'group-free'        => null,
            'group-web-hosting' => null,
            'group-game'        => 'game-',
            'group-software'    => 'software-',
            'group-bot'         => 'bot-',
            'group-database'    => 'database-',
            'group-voice'       => 'voice-',
            'group-monitoring'  => 'monitoring-',
            'group-others'      => null,
        ];

        $rows = DB::table('categories')->whereNotNull('parent_id')->get(['id', 'link', 'parent_id']);
        foreach ($rows as $row) {
            $groupLink = $groupById[$row->parent_id] ?? null;
            $prefix = $prefixByGroup[$groupLink] ?? null;
            if (!$prefix) {
                continue;
            }
            if (str_starts_with($row->link, $prefix)) {
                continue;
            }

            $aliases = [$row->link];
            $newSlug = $prefix . preg_replace('/^(game|software|bot|database|voice|monitoring)-/', '', $row->link);

            $taken = DB::table('categories')->where('link', $newSlug)->where('id', '!=', $row->id)->exists();
            if ($taken) {
                continue;
            }

            DB::table('categories')
                ->where('id', $row->id)
                ->update([
                    'link'       => $newSlug,
                    'aliases'    => json_encode($aliases),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        $rows = DB::table('categories')->whereNotNull('aliases')->get(['id', 'link', 'aliases']);
        foreach ($rows as $row) {
            $aliases = json_decode($row->aliases, true) ?? [];
            if (empty($aliases)) {
                continue;
            }
            DB::table('categories')->where('id', $row->id)->update([
                'link'    => $aliases[0],
                'aliases' => null,
            ]);
        }

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'aliases')) {
                $table->dropColumn('aliases');
            }
        });
    }
};
