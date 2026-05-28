<?php

namespace App\Http\Controllers;

use App\Enums\Body;
use App\Enums\Fuel;
use App\Enums\Type;
use App\Http\Requests\EquipmentRequest;
use App\Models\Engine;
use App\Models\Equipment;
use App\Models\Model as CarModel;
use App\Models\Order;
use App\Models\Request as LeadRequest;
use App\Models\Supply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $equipments = Equipment::search($request->search, [
            '' => ['id', 'name'],
            'engine' => ['name'],
            'model' => ['name'],
            'model.brand' => ['name'],
        ])
            ->with('engine', 'model.brand')
            ->orderByDesc('id')
            ->get();
        $types = Type::array();
        $bodies = Body::array();

        return Inertia::render('CRM/Equipments/Index', compact('equipments', 'types', 'bodies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $engines = Engine::orderBy('name')->get();
        $models = CarModel::with('brand')->orderBy('name')->get();
        $types = Type::array();
        $bodies = Body::array();
        $fuels = Fuel::array();

        return Inertia::render('CRM/Equipments/Create', compact('engines', 'models', 'types', 'bodies', 'fuels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EquipmentRequest $request)
    {
        $data = $this->withRequiredGenerationId($this->withLegacyDefaults($request->validated()));

        if ($request->boolean('wizard')) {
            $equipment = DB::transaction(function () use ($data) {
                return Equipment::create($data);
            });

            return redirect()
                ->route('crm.cars.create')
                ->with('wizard_created_equipment_id', $equipment->id);
        }

        $equipment = DB::transaction(function () use ($request, $data) {
            $equipment = Equipment::create($data);

            $supply = Supply::create([
                'user_id' => $request->user()->id,
                'price' => null,
                'equipment_id' => $equipment->id,
                'created_at' => now(),
                'supplied_at' => now(),
            ]);

            $supply->car()->create([
                'supply_id' => $supply->id,
                'manager_id' => $request->user()->id,
                'status' => 'SELLING',
            ]);

            return $equipment;
        });

        return redirect()->route('crm.equipments.show', ['id' => $equipment->id]);
    }

    private function withRequiredGenerationId(array $data): array
    {
        if (!Schema::hasColumn('equipments', 'generation_id')) {
            return $data;
        }

        if (!empty($data['generation_id'])) {
            return $data;
        }

        $modelId = $data['model_id'] ?? null;
        if (!$modelId) {
            throw ValidationException::withMessages([
                'model_id' => 'Не удалось определить генерацию без модели.',
            ]);
        }

        if (!Schema::hasTable('generations')) {
            throw ValidationException::withMessages([
                'generation_id' => 'Таблица generations отсутствует. Невозможно создать комплектацию.',
            ]);
        }

        $generationId = DB::table('generations')
            ->where('model_id', $modelId)
            ->orderBy('to')
            ->orderByDesc('from')
            ->orderByDesc('restyling')
            ->orderByDesc('id')
            ->value('id');

        if (!$generationId) {
            // Wizard flow has no UI for generations yet; create a minimal default generation.
            $generationId = DB::table('generations')->insertGetId([
                'model_id' => $modelId,
                'from' => now()->toDateString(),
                'to' => null,
                'number' => null,
                'restyling' => 0,
            ]);
        }

        $data['generation_id'] = $generationId;

        return $data;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $equipment = Equipment::with('engine', 'model.brand')->findOrFail($id);
        $types = Type::array();
        $bodies = Body::array();

        return Inertia::render('CRM/Equipments/Show', compact('equipment', 'types', 'bodies'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $equipment = Equipment::with('engine', 'model.brand')->findOrFail($id);
        $engines = Engine::orderBy('name')->get();
        $models = CarModel::with('brand')->orderBy('name')->get();
        $types = Type::array();
        $bodies = Body::array();
        $fuels = Fuel::array();

        return Inertia::render('CRM/Equipments/Edit', compact('equipment', 'engines', 'models', 'types', 'bodies', 'fuels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EquipmentRequest $request, string $id)
    {
        $equipment = Equipment::findOrFail($id);
        DB::transaction(function () use ($equipment, $request) {
            $equipment->update($this->withLegacyDefaults($request->validated()));

            if (!$equipment->supplies()->exists()) {
                $supply = Supply::create([
                    'user_id' => $request->user()->id,
                    'price' => null,
                    'equipment_id' => $equipment->id,
                    'created_at' => now(),
                    'supplied_at' => now(),
                ]);

                $supply->car()->create([
                    'supply_id' => $supply->id,
                    'manager_id' => $request->user()->id,
                    'status' => 'SELLING',
                ]);
            }
        });

        return redirect()->route('crm.equipments.show', ['id' => $equipment->id]);
    }

    private function withLegacyDefaults(array $data): array
    {
        if (Schema::hasColumn('equipments', 'max_speed')) {
            $data['max_speed'] = $data['max_speed'] ?? 0;
        }
        if (Schema::hasColumn('equipments', 'acceleration_time')) {
            $data['acceleration_time'] = $data['acceleration_time'] ?? 0;
        }
        if (Schema::hasColumn('equipments', 'fuel_consumption_90')) {
            $data['fuel_consumption_90'] = $data['fuel_consumption_90'] ?? 0;
        }
        if (Schema::hasColumn('equipments', 'fuel_consumption_120')) {
            $data['fuel_consumption_120'] = $data['fuel_consumption_120'] ?? 0;
        }
        if (Schema::hasColumn('equipments', 'fuel_consumption_city')) {
            $data['fuel_consumption_city'] = $data['fuel_consumption_city'] ?? 0;
        }

        // Compatibility for DBs where old columns were not dropped yet.
        if (Schema::hasColumn('equipments', 'length')) {
            $data['length'] = $data['length'] ?? 0;
        }
        if (Schema::hasColumn('equipments', 'width')) {
            $data['width'] = $data['width'] ?? 0;
        }
        if (Schema::hasColumn('equipments', 'height')) {
            $data['height'] = $data['height'] ?? 0;
        }
        if (Schema::hasColumn('equipments', 'tires_name')) {
            $data['tires_name'] = $data['tires_name'] ?? '-';
        }

        return $data;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $equipment = Equipment::with('supplies.car.photos')->findOrFail($id);

        $carIds = $equipment->supplies
            ->pluck('car.id')
            ->filter()
            ->values();

        if ($carIds->isNotEmpty()) {
            $hasOrders = Order::whereIn('car_id', $carIds)->exists();
            $hasRequests = LeadRequest::whereIn('car_id', $carIds)->exists();

            if ($hasOrders || $hasRequests) {
                return back()->withErrors([
                    'delete' => 'Нельзя удалить комплектацию, у которой есть связанные заявки или заказы.',
                ]);
            }
        }

        DB::transaction(function () use ($equipment) {
            $equipment->supplies->each(function ($supply) {
                if ($supply->car) {
                    $supply->car->photos->each->delete();
                    $supply->car->delete();
                }

                $supply->delete();
            });

            $equipment->delete();
        });

        return redirect()->route('crm.equipments.index');
    }
}
