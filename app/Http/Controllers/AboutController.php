<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Inertia\Inertia;

class AboutController extends Controller
{
    public function index()
    {
        $cars = Car::where('status', 'SELLING')->count();

        return Inertia::render('About', [
            'cars' => $cars,
        ]);
    }
}
