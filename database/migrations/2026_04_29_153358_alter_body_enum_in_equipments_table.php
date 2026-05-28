<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE equipments MODIFY body ENUM(
            'SEDAN', 'HATCHBACK', 'SUV', 'CROSSOVER', 'SPORTS_CAR', 'COUPE', 'STATION_WAGON', 'CONVERTIBLE',
            'MINI_VAN', 'MINIVAN', 'VAN', 'PICKUP_TRUCK', 'MUSCLE_CAR', 'SUPER_CAR', 'LIMOUSINE', 'MONSTER_TRUCK',
            'JEEP', 'ROADSTER', 'OTHER'
        )");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE equipments MODIFY body ENUM(
            'SEDAN', 'HATCHBACK', 'SUV', 'CROSSOVER', 'SPORTS_CAR', 'COUPE', 'STATION_WAGON', 'CONVERTIBLE',
            'MINI_VAN', 'VAN', 'PICKUP_TRUCK', 'MUSCLE_CAR', 'SUPER_CAR', 'LIMOUSINE', 'MONSTER_TRUCK',
            'JEEP', 'ROADSTER', 'OTHER'
        )");
    }
};