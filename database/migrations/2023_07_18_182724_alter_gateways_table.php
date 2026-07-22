<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('gateways', 'blade_edit_path')) {
            Schema::table('gateways', function (Blueprint $table) {
                $table->string('blade_edit_path')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('gateways', 'blade_edit_path')) {
            Schema::table('gateways', function (Blueprint $table) {
                $table->dropColumn('blade_edit_path');
            });
        }
    }
};
