<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            if (!Schema::hasColumn('team_members', 'photo_focus_x')) {
                $table->unsignedTinyInteger('photo_focus_x')->default(50)->after('photo');
            }
            if (!Schema::hasColumn('team_members', 'photo_focus_y')) {
                $table->unsignedTinyInteger('photo_focus_y')->default(50)->after('photo_focus_x');
            }
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn(['photo_focus_x', 'photo_focus_y']);
        });
    }
};
