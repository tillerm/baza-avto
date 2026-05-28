<?php

namespace App\Http\Controllers;

use App\Enums\Body;
use App\Enums\Fuel;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CatalogController extends Controller
{
    private const TAGANROG_COORDINATES = [
        'lat' => 47.2362,
        'lon' => 38.8969,
    ];

    private const DELIVERY_BASE_PRICE = 100000;
    private const DELIVERY_PRICE_PER_KM = 25;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cars = Car::search($request->search, [
            '' => ['release_date'],
            'supply.equipment' => ['name'],
            'supply.equipment.engine' => ['name'],
            'supply.equipment.model' => ['name'],
            'supply.equipment.model.brand' => ['name'],
        ])
            ->filter($request->all())
            ->with('photos', 'supply.equipment.engine', 'supply.equipment.model.brand', 'manager')
            ->whereIn('status', ['SELLING', 'SOLD'])
            ->orderByRaw("CASE WHEN status = 'SELLING' THEN 0 ELSE 1 END")
            ->orderByDesc('pinned')
            ->orderByDesc('id')
            ->get();
        $brands = Brand::orderBy('name')->get();
        $models = Model::where('brand_id', '=', $request->brand)->orderBy('name')->get();
        return Inertia::render('Catalog/Index', compact('cars', 'brands', 'models'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $car = Car::with('photos', 'supply.equipment.engine', 'supply.equipment.model.brand', 'manager')->whereIn('status', ['SELLING', 'SOLD'])->findOrFail($id);
        $fuels = Fuel::array();
        $bodies = Body::array();

        return Inertia::render('Catalog/Show', compact('car', 'fuels', 'bodies'));
    }

    public function calculator()
    {
        return Inertia::render('Catalog/Calculator');
    }

    public function calculateDeliveryEstimate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'destination' => ['required', 'string', 'max:255'],
        ]);

        $destination = $this->resolveDestination($validated['destination']);
        $distance = $this->resolveDistanceFromTaganrog($destination);
        $price = self::DELIVERY_BASE_PRICE + ($distance['distance_km'] * self::DELIVERY_PRICE_PER_KM);

        return response()->json([
            'origin' => 'Таганрог',
            'destination' => $destination['display_name'],
            'distance_km' => $distance['distance_km'],
            'price' => $price,
            'is_approximate' => $distance['is_approximate'],
            'pricing' => [
                'base_price' => self::DELIVERY_BASE_PRICE,
                'price_per_km' => self::DELIVERY_PRICE_PER_KM,
                'distance_price' => $distance['distance_km'] * self::DELIVERY_PRICE_PER_KM,
            ],
        ]);
    }

    private function resolveDestination(string $destination): array
    {
        $queries = [
            ['countrycodes' => 'ru'],
            [],
        ];

        foreach ($queries as $query) {
            $response = Http::acceptJson()
                ->withUserAgent($this->buildGeocoderUserAgent())
                ->timeout(10)
                ->get('https://nominatim.openstreetmap.org/search', array_merge([
                    'q' => $destination,
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'addressdetails' => 1,
                    'accept-language' => 'ru',
                ], $query));

            if (!$response->successful()) {
                continue;
            }

            $item = $response->json('0');

            if (!$item || !isset($item['lat'], $item['lon'])) {
                continue;
            }

            return [
                'display_name' => $item['display_name'] ?? $destination,
                'lat' => (float) $item['lat'],
                'lon' => (float) $item['lon'],
            ];
        }

        throw ValidationException::withMessages([
            'destination' => 'Не удалось найти адрес. Укажите город или адрес точнее.',
        ]);
    }

    private function resolveDistanceFromTaganrog(array $destination): array
    {
        $origin = self::TAGANROG_COORDINATES;
        $coordinates = sprintf(
            '%s,%s;%s,%s',
            $origin['lon'],
            $origin['lat'],
            $destination['lon'],
            $destination['lat'],
        );

        $response = Http::acceptJson()
            ->timeout(10)
            ->get("https://router.project-osrm.org/route/v1/driving/{$coordinates}", [
                'alternatives' => 'false',
                'overview' => 'false',
                'steps' => 'false',
            ]);

        if ($response->successful() && $response->json('routes.0.distance')) {
            return [
                'distance_km' => (int) ceil($response->json('routes.0.distance') / 1000),
                'is_approximate' => false,
            ];
        }

        return [
            'distance_km' => (int) ceil($this->haversineDistanceKm(
                $origin['lat'],
                $origin['lon'],
                $destination['lat'],
                $destination['lon'],
            ) * 1.2),
            'is_approximate' => true,
        ];
    }

    private function haversineDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $latFrom = deg2rad($lat1);
        $latTo = deg2rad($lat2);
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $angle = 2 * asin(sqrt(
            sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2
        ));

        return $earthRadius * $angle;
    }

    private function buildGeocoderUserAgent(): string
    {
        $appName = trim((string) config('app.name', 'БазаАвто'));
        $appUrl = trim((string) config('app.url', 'https://baza-avto.ru'));

        return "{$appName}/1.0 ({$appUrl})";
    }

    /**
     * Pin car to top (once per day per manager)
     */
    public function pin(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !$user->isManager()) {
            abort(403);
        }

        $car = Car::findOrFail($id);
        if ($car->manager_id !== $user->id) {
            abort(403);
        }

        $cacheKey = 'car_pin_' . $user->id;
        $pins = cache()->get($cacheKey, []);
        $now = now();
        // Удаляем старые записи
        $pins = array_filter($pins, fn($ts) => $now->diffInHours($ts) < 24);
        if (count($pins) >= 2) {
            return response()->json(['error' => 'Вы уже поднимали 2 машины за сутки'], 429);
        }
        if (!empty($pins) && $now->diffInMinutes(max($pins)) < 60) {
            return response()->json(['error' => 'Минимальная пауза между поднятиями — 1 час'], 429);
        }
        $car->pinned = true;
        $car->save();
        $pins[] = $now;
        cache()->put($cacheKey, $pins, now()->addDay());
        return response()->json(['success' => true]);
    }
}
