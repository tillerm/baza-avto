<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

class PopularCarsSeeder extends Seeder
{
    /**
     * Помечает до 10 существующих авто как популярные (pinned = true).
     */
    public function run(): void
    {
        Car::query()
            ->orderByDesc('pinned')
            ->orderByDesc('release_date')
            ->orderByDesc('id')
            ->take(10)
            ->update(['pinned' => true]);
    }
}
