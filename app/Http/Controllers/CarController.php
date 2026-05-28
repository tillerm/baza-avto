<?php

namespace App\Http\Controllers;

use App\Enums\CarStatus;
use App\Exports\SellingCarsExport;
use App\Http\Requests\CarRequest;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Model;
use App\Models\Supply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = $request->user()?->id;

        $cars = Car::search($request->search, [
            '' => ['id', 'state_number', 'vin', 'release_date'],
            'supply.equipment' => ['name'],
            'supply.equipment.engine' => ['name'],
            'manager' => ['name', 'telegram_username'],
        ])
            ->when($request->user()?->isManager(), function ($query) use ($userId) {
                $query->where(function ($ownedQuery) use ($userId) {
                    $ownedQuery->where('manager_id', $userId)
                        ->orWhereHas('supply', function ($supplyQuery) use ($userId) {
                            $supplyQuery->where('user_id', $userId);
                        });
                });
            })
            ->filter($request->all())
            ->with('supply.equipment.engine', 'supply.user', 'manager')
            ->orderByDesc('id')
            ->get();
        $brands = Brand::orderBy('name')->get();
        $models = Model::where('brand_id', '=', $request->brand)->orderBy('name')->get();
        $statuses = CarStatus::array();

        return Inertia::render('CRM/Cars/Index', compact('cars', 'brands', 'models', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'supply_price' => 'nullable|numeric|between:0,9999999999.99',

            'car_price' => 'nullable|numeric|between:0,9999999999.99',
            'customs' => 'nullable|numeric|between:0,9999999999.99',

            'vin' => 'nullable|string|max:17',
            'mileage' => 'nullable|numeric|max:4294967295',
            'color' => 'nullable|string|max:255',
            'state_number' => 'nullable|string|max:10',
            'release_date' => 'nullable|date',
        ]);

        $userId = $request->user()->id;

        $car = DB::transaction(function () use ($validated, $userId) {
            $supply = Supply::create([
                'user_id' => $userId,
                'price' => $validated['supply_price'] ?? null,
                'equipment_id' => $validated['equipment_id'],
                'created_at' => now(),
                'supplied_at' => now(),
            ]);

            $carData = [
                'supply_id' => $supply->id,
                'manager_id' => $userId,
                'status' => CarStatus::SELLING->name,
                'vin' => $validated['vin'] ?? null,
                'mileage' => $validated['mileage'] ?? null,
                'color' => $validated['color'] ?? null,
                'state_number' => $validated['state_number'] ?? null,
                'release_date' => $validated['release_date'] ?? null,
                'car_price' => $validated['car_price'] ?? null,
                'customs' => $validated['customs'] ?? null,
            ];

            $carPrice = isset($carData['car_price']) ? (float) $carData['car_price'] : 0.0;
            $customs = isset($carData['customs']) ? (float) $carData['customs'] : 0.0;
            $carData['price'] = $carPrice + $customs;

            return $supply->car()->create($carData);
        });

        return redirect()
            ->route('crm.cars.show', ['id' => $car->id])
            ->with('flash', [
                'toast' => [
                    'severity' => 'success',
                    'summary' => 'Успешно',
                    'detail' => 'Автомобиль создан',
                    'life' => 3000,
                ],
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $car = Car::query()
            ->with('supply.equipment.engine', 'supply.user', 'manager', 'photos')
            ->where('id', $id)
            ->when($request->user()?->isManager(), function ($query) use ($request) {
                $userId = $request->user()->id;

                $query->where(function ($ownedQuery) use ($userId) {
                    $ownedQuery->where('manager_id', $userId)
                        ->orWhereHas('supply', function ($supplyQuery) use ($userId) {
                            $supplyQuery->where('user_id', $userId);
                        });
                });
            })
            ->firstOrFail();
        $statuses = CarStatus::array();

        return Inertia::render('CRM/Cars/Show', compact('car', 'statuses'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $car = Car::query()
            ->with('manager')
            ->where('id', $id)
            ->when($request->user()?->isManager(), function ($query) use ($request) {
                $userId = $request->user()->id;

                $query->where(function ($ownedQuery) use ($userId) {
                    $ownedQuery->where('manager_id', $userId)
                        ->orWhereHas('supply', function ($supplyQuery) use ($userId) {
                            $supplyQuery->where('user_id', $userId);
                        });
                });
            })
            ->firstOrFail();
        $statuses = CarStatus::array();

        return Inertia::render('CRM/Cars/Edit', compact('car', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CarRequest $request, string $id)
    {
        $car = Car::query()
            ->where('id', $id)
            ->when($request->user()?->isManager(), function ($query) use ($request) {
                $userId = $request->user()->id;

                $query->where(function ($ownedQuery) use ($userId) {
                    $ownedQuery->where('manager_id', $userId)
                        ->orWhereHas('supply', function ($supplyQuery) use ($userId) {
                            $supplyQuery->where('user_id', $userId);
                        });
                });
            })
            ->firstOrFail();
        $data = $request->validated();

        if ($request->user()?->isManager()) {
            unset($data['pinned']);
        }

        // keep backward compatibility: compute total price from car_price + customs if provided
        $carPrice = isset($data['car_price']) ? (float) $data['car_price'] : ($request->input('price') ?? 0);
        $customs = isset($data['customs']) ? (float) $data['customs'] : 0;
        $data['price'] = $carPrice + $customs;
        $car->update($data);

        return redirect()->route('crm.cars.show', ['id' => $car->id]);
    }

    public function markSold(Request $request, string $id)
    {
        $userId = $request->user()->id;

        $car = Car::query()
            ->where('id', $id)
            ->where(function ($query) use ($userId) {
                $query->where('manager_id', $userId)
                    ->orWhereHas('supply', function ($supplyQuery) use ($userId) {
                        $supplyQuery->where('user_id', $userId);
                    });
            })
            ->firstOrFail();

        $car->update([
            'status' => CarStatus::SOLD->name,
        ]);

        return back();
    }

    public function downloadSellingCarsReport()
    {
        return Excel::download(new SellingCarsExport, 'Номенклатура.xlsx');
    }
}
