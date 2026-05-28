<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SampleCarPhotosSeeder extends Seeder
{
    private const PHOTOS_PER_CAR = 3;
    private const PLACEHOLDER_DIR = 'seed/cars';

    public function run(): void
    {
        $disk = Storage::disk('public');

        $pool = collect($disk->files())
            ->filter(fn (string $path) => $this->isImagePath($path))
            ->values()
            ->all();

        if (count($pool) === 0) {
            $pool = $this->ensurePlaceholderPool($disk, 12);
        }

        if (count($pool) === 0) {
            return;
        }

        sort($pool);

        $supportsPrimary = Schema::hasColumn('cars_photos', 'is_primary');

        $cars = Car::query()->get();
        $poolCount = count($pool);

        foreach ($cars as $carIndex => $car) {
            if ($car->photos()->exists()) {
                continue;
            }

            $startIndex = (($car->id ?? 0) + $carIndex) % $poolCount;

            for ($i = 0; $i < self::PHOTOS_PER_CAR; $i++) {
                $photoPath = $pool[($startIndex + $i) % $poolCount];

                $payload = [
                    'car_id' => $car->id,
                    'photo' => $photoPath,
                ];

                if ($supportsPrimary) {
                    $payload['is_primary'] = $i === 0;
                }

                CarPhoto::create($payload);
            }
        }
    }

    private function isImagePath(string $path): bool
    {
        if ($path === '.gitignore') {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg'], true);
    }

    private function ensurePlaceholderPool($disk, int $count): array
    {
        $disk->makeDirectory(self::PLACEHOLDER_DIR);

        $paths = [];
        for ($i = 1; $i <= $count; $i++) {
            $label = sprintf('Demo Photo %02d', $i);
            $relativePath = sprintf('%s/car-%02d.svg', self::PLACEHOLDER_DIR, $i);

            if (! $disk->exists($relativePath)) {
                $disk->put($relativePath, $this->buildPlaceholderSvg($label));
            }

            $paths[] = $relativePath;
        }

        return $paths;
    }

    private function buildPlaceholderSvg(string $label): string
    {
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800">
  <rect width="1200" height="800" fill="#111827" />
  <rect x="60" y="60" width="1080" height="680" rx="48" fill="rgba(255,255,255,0.06)" stroke="rgba(255,255,255,0.18)" stroke-width="6" />
  <text x="100" y="180" fill="rgba(255,255,255,0.92)" font-family="Arial, sans-serif" font-size="56" font-weight="700">{$safeLabel}</text>
  <text x="100" y="255" fill="rgba(255,255,255,0.7)" font-family="Arial, sans-serif" font-size="28">Seeded locally • Placeholder image</text>
</svg>
SVG;
    }
}
