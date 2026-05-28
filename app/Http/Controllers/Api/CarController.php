<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;

class CarController extends Controller
{
    /**
     * Return a list of all cars with related data.
     */
    public function index()
    {
        $cars = Car::with(['supply.equipment.engine', 'photos'])
            ->orderByDesc('id')
            ->get();

        return response()->json($cars);
    }
}
