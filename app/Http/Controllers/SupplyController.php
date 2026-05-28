<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Supply;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $supplies = Supply::search($request->search, [
            '' => ['id'],
            'equipment' => ['name'],
            'equipment.engine' => ['name'],
            'equipment.model' => ['name'],
            'equipment.model.brand' => ['name'],
            'user' => ['name'],
        ])
            ->filter($request->all())
            ->with('equipment.engine', 'equipment.model.brand', 'user')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('CRM/Supplies/Index', compact('supplies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $equipments = Equipment::with('engine', 'model.brand')->orderBy('name')->get();

        return Inertia::render('CRM/Supplies/Create', compact('equipments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'price' => 'nullable|numeric|between:0,9999999999.99',
            'equipment_id' => 'required|exists:equipments,id',
        ]);
        $supply = Supply::create($validated +
            [
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'supplied_at' => now(),
            ]);
        $supply->car()->create([
            'supply_id' => $supply->id,
            'manager_id' => $request->user()->id,
            'status' => 'SELLING',
        ]);

        return redirect()->route('crm.supplies.show', ['id' => $supply->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supply = Supply::with('equipment.engine', 'equipment.model.brand', 'user', 'car')->findOrFail($id);

        return Inertia::render('CRM/Supplies/Show', compact('supply'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $supply = Supply::with('equipment.engine', 'equipment.model.brand')->findOrFail($id);
        $equipments = Equipment::with('engine', 'model.brand')->orderBy('name')->get();

        return Inertia::render('CRM/Supplies/Edit', compact('supply', 'equipments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $supply = Supply::findOrFail($id);
        $validated = $request->validate([
            'price' => 'nullable|numeric|between:0,9999999999.99',
            'equipment_id' => 'required|exists:equipments,id',
        ]);
        $supply->update($validated + ['supplied_at' => $supply->supplied_at ?? now()]);

        // Legacy safety: if a car relation is missing, recreate it as visible in catalog.
        if (! $supply->car()->exists()) {
            $supply->car()->create([
                'supply_id' => $supply->id,
                'manager_id' => $supply->user_id,
                'status' => 'SELLING',
            ]);
        }

        return redirect()->route('crm.supplies.show', ['id' => $supply->id]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $supply = Supply::with('car.photos')->findOrFail($id);
        $supply->car->photos->each(function ($photo) {
            $photo->delete();
        });
        $supply->car->delete();
        $supply->delete();

        return redirect()->route('crm.supplies.index');
    }
}
