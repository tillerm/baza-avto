<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandLogosSeeder extends Seeder
{
    /**
     * Test brand logos. Uses Clearbit Logo API (free, CORS-enabled, no auth).
     * Run via: php artisan db:seed --class=BrandLogosSeeder
     */
    public function run(): void
    {
        $logos = [
            'BYD'      => 'https://logo.clearbit.com/byd.com',
            'Changan'  => 'https://logo.clearbit.com/globalchangan.com',
            'Chery'    => 'https://logo.clearbit.com/cheryinternational.com',
            'Exeed'    => 'https://logo.clearbit.com/exeedcars.com',
            'Geely'    => 'https://logo.clearbit.com/geely.com',
            'Haval'    => 'https://logo.clearbit.com/haval-global.com',
            'Hyundai'  => 'https://logo.clearbit.com/hyundai.com',
            'Kia'      => 'https://logo.clearbit.com/kia.com',
            'Mazda'    => 'https://logo.clearbit.com/mazda.com',
            'Toyota'   => 'https://logo.clearbit.com/toyota.com',
        ];

        foreach ($logos as $name => $url) {
            Brand::where('name', $name)->update(['logo' => $url]);
        }
    }
}
