<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars_photos', function (Blueprint $table) {
            if (! Schema::hasColumn('cars_photos', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('photo');
            }
        });

        $carIds = DB::table('cars_photos')
            ->select('car_id')
            ->groupBy('car_id')
            ->pluck('car_id');

        foreach ($carIds as $carId) {
            $firstPhotoId = DB::table('cars_photos')
                ->where('car_id', $carId)
                ->orderBy('id')
                ->value('id');

            if ($firstPhotoId) {
                DB::table('cars_photos')
                    ->where('id', $firstPhotoId)
                    ->update(['is_primary' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('cars_photos', function (Blueprint $table) {
            if (Schema::hasColumn('cars_photos', 'is_primary')) {
                $table->dropColumn('is_primary');
            }
        });
    }
};
