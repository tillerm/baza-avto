<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['A', 'B', 'C', 'D', 'E']);
            $table->string('engine_model');
            $table->string('engine_number');
            $table->float('engine_capacity')->unsigned();
            $table->tinyInteger('doors_count')->unsigned();
            $table->tinyInteger('seats_count')->unsigned();
            $table->enum('fuel', ['PETROL', 'DIESEL', 'ELECTRICITY', 'GAS', 'OTHER']);
            $table->foreignId('brand_id')->constrained('brands')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('country_id')->constrained('countries')->restrictOnDelete()->cascadeOnUpdate();
            $table->enum('body', ['SEDAN', 'HATCHBACK', 'SUV', 'CROSSOVER', 'SPORTS_CAR', 'COUPE', 'STATION_WAGON', 'CONVERTIBLE', 'MINI_VAN', 'VAN', 'PICKUP_TRUCK', 'MUSCLE_CAR', 'SUPER_CAR', 'LIMOUSINE', 'MONSTER_TRUCK', 'JEEP', 'ROADSTER', 'OTHER']);
            $table->date('release_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('models');
    }
};
