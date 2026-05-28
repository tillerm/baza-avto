<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            if (! Schema::hasColumn('team_members', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        });

        $teamMembers = DB::table('team_members')
            ->whereNull('user_id')
            ->whereNotNull('telegram_username')
            ->select('id', 'telegram_username')
            ->get();

        foreach ($teamMembers as $teamMember) {
            $username = ltrim((string) $teamMember->telegram_username, '@');

            if ($username === '') {
                continue;
            }

            $userId = DB::table('users')
                ->whereRaw('LOWER(REPLACE(telegram_username, "@", "")) = ?', [strtolower($username)])
                ->value('id');

            if ($userId) {
                DB::table('team_members')
                    ->where('id', $teamMember->id)
                    ->update(['user_id' => $userId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            if (Schema::hasColumn('team_members', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
