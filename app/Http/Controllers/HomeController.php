<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\TeamMember;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $popularCars = Car::with([
            'photos',
            'supply.equipment.engine',
        ])
            ->orderByDesc('pinned')
            ->orderByDesc('release_date')
            ->orderByDesc('id')
            ->take(3)
            ->get()
            ->map(function (Car $car) {
                $equipment = $car->supply->equipment;

                return [
                    'id' => $car->id,
                    'name' => $equipment->name,
                    'release_date' => $car->release_date,
                    'engine' => $equipment->engine->capacity ?? null,
                    'drive' => null,
                    'transmission' => null,
                    'photo' => $car->photos->first()?->photo,
                    'pinned' => $car->pinned,
                ];
            });

        $teamMembers = TeamMember::where('is_active', true)
            ->with('user')
            ->orderByDesc('position')
            ->orderByDesc('id')
            ->get()
            ->map(function (TeamMember $member) {
                $linkedUser = $member->user;

                return [
                    'id' => $member->id,
                    'name' => $linkedUser?->name ?? $member->name,
                    'role' => $member->role,
                    'city' => $member->city,
                    'phone' => $linkedUser?->phone ?? $member->phone,
                    'telegram_username' => $linkedUser?->telegram_username ?? $member->telegram_username,
                    'description' => $member->description,
                    'photo' => $member->photo,
                    'photo_focus_x' => $member->photo_focus_x,
                    'photo_focus_y' => $member->photo_focus_y,
                ];
            });

        return Inertia::render('Home', [
            'popularCars' => $popularCars,
            'teamMembers' => $teamMembers,
        ]);
    }
}
