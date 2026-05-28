<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Engine;
use App\Models\Equipment;
use App\Models\Model as CarModel;
use App\Models\Brand;
use App\Models\Country;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleCarsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first() ?? User::factory()->create([
            'name' => 'Demo Admin',
            'email' => 'demo@example.com',
        ]);

        $country = Country::firstOrCreate(['name' => 'Китай']);

        $brandLogos = [
            'BYD' => 'https://logo.clearbit.com/byd.com',
            'Exeed' => 'https://logo.clearbit.com/exeed-global.com',
            'Geely' => 'https://logo.clearbit.com/geely.com',
            'Changan' => 'https://logo.clearbit.com/changan.com.cn',
            'Haval' => 'https://logo.clearbit.com/haval.com',
            'Chery' => 'https://logo.clearbit.com/cheryinternational.com',
            'Toyota' => 'https://logo.clearbit.com/toyota.com',
            'Mazda' => 'https://logo.clearbit.com/mazda.com',
            'Hyundai' => 'https://logo.clearbit.com/hyundai.com',
            'Kia' => 'https://logo.clearbit.com/kia.com',
        ];

        $samples = [
            ['brand' => 'BYD', 'model' => 'Song Plus', 'year' => '2021-09-01', 'engine' => ['1.5', 4, 110, 150, 'PETROL'], 'body' => 'CROSSOVER'],
            ['brand' => 'Exeed', 'model' => 'TX', 'year' => '2019-07-01', 'engine' => ['1.6', 4, 197, 260, 'PETROL'], 'body' => 'SUV'],
            ['brand' => 'Geely', 'model' => 'Tugella', 'year' => '2020-02-01', 'engine' => ['2.0', 4, 238, 350, 'PETROL'], 'body' => 'CROSSOVER'],
            ['brand' => 'Changan', 'model' => 'CS75 Plus', 'year' => '2022-03-01', 'engine' => ['2.0', 4, 233, 360, 'PETROL'], 'body' => 'SUV'],
            ['brand' => 'Haval', 'model' => 'F7x', 'year' => '2021-05-01', 'engine' => ['2.0', 4, 190, 340, 'PETROL'], 'body' => 'CROSSOVER'],
            ['brand' => 'Chery', 'model' => 'Tiggo 8 Pro', 'year' => '2022-01-01', 'engine' => ['2.0', 4, 197, 375, 'PETROL'], 'body' => 'SUV'],
            ['brand' => 'Toyota', 'model' => 'Corolla', 'year' => '2020-09-01', 'engine' => ['1.6', 4, 122, 153, 'PETROL'], 'body' => 'SEDAN'],
            ['brand' => 'Mazda', 'model' => 'CX-5', 'year' => '2021-04-01', 'engine' => ['2.5', 4, 194, 258, 'PETROL'], 'body' => 'CROSSOVER'],
            ['brand' => 'Hyundai', 'model' => 'Tucson', 'year' => '2022-02-01', 'engine' => ['2.0', 4, 150, 192, 'PETROL'], 'body' => 'CROSSOVER'],
            ['brand' => 'Kia', 'model' => 'Sportage', 'year' => '2021-08-01', 'engine' => ['2.4', 4, 184, 237, 'PETROL'], 'body' => 'CROSSOVER'],
        ];

        foreach ($samples as $index => $sample) {
            $brand = Brand::firstOrCreate(
                ['name' => $sample['brand']],
                ['country_id' => $country->id]
            );

            if (empty($brand->logo) && isset($brandLogos[$sample['brand']])) {
                $brand->logo = $brandLogos[$sample['brand']];
                $brand->save();
            }

            $model = CarModel::firstOrCreate(
                ['name' => $sample['model'], 'brand_id' => $brand->id]
            );

            $engine = Engine::create([
                'name' => $sample['model'] . ' ' . $sample['engine'][0] . 'L',
                'capacity' => $sample['engine'][0],
                'fuel' => $sample['engine'][4],
            ]);

            $equipment = Equipment::create([
                'name' => $sample['model'] . ' Base',
                'type' => 'C',
                'doors_count' => 5,
                'seats_count' => 5,
                'body' => $sample['body'],
                'engine_id' => $engine->id,
            ]);

            $supply = Supply::create([
                'user_id' => $user->id,
                'price' => 0,
                'equipment_id' => $equipment->id,
                'supplier_id' => null,
                'created_at' => now(),
                'supplied_at' => now(),
            ]);

            Car::create([
                'price' => rand(2000000, 5000000),
                'status' => 'SELLING',
                'vin' => null,
                'mileage' => rand(1000, 60000),
                'color' => 'Черный',
                'supply_id' => $supply->id,
                'release_date' => $sample['year'],
                'pinned' => $index < 6, // первые 6 идут в популярные
            ]);
        }
    }
}
