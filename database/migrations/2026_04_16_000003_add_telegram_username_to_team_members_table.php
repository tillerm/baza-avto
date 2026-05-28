<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            if (! Schema::hasColumn('team_members', 'telegram_username')) {
                $table->string('telegram_username')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            if (Schema::hasColumn('team_members', 'telegram_username')) {
                $table->dropColumn('telegram_username');
            }
        });
    }
};
