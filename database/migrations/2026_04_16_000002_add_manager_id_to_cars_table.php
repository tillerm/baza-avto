<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            if (! Schema::hasColumn('cars', 'manager_id')) {
                $table->foreignId('manager_id')
                    ->nullable()
                    ->after('supply_id')
                    ->constrained('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        });

        DB::table('cars')
            ->join('supplies', 'supplies.id', '=', 'cars.supply_id')
            ->whereNull('cars.manager_id')
            ->update([
                'cars.manager_id' => DB::raw('supplies.user_id'),
            ]);
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            if (Schema::hasColumn('cars', 'manager_id')) {
                $table->dropConstrainedForeignId('manager_id');
            }
        });
    }
};
