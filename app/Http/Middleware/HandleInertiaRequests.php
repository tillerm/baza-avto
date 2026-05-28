<?php

namespace App\Http\Middleware;

use App\Models\Car;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'access' => [
                'isManager' => fn () => (bool) ($request->user()?->isManager() ?? false),
            ],
            'profileCars' => function () use ($request) {
                if (! $request->routeIs('profile.show') || ! $request->user()) {
                    return [];
                }

                $userId = $request->user()->id;

                return Car::query()
                    ->with(['supply.equipment.engine'])
                    ->where('manager_id', $userId)
                    ->orWhereHas('supply', function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                    ->orderByDesc('id')
                    ->get()
                    ->map(function (Car $car) {
                        $equipment = $car->supply?->equipment;

                        return [
                            'id' => $car->id,
                            'status' => $car->status,
                            'equipment' => $equipment?->name,
                            'brand' => null,
                            'model' => null,
                            'engine' => $equipment?->engine?->name,
                            'release_date' => $car->release_date,
                            'vin' => $car->vin,
                            'color' => $car->color,
                            'mileage' => $car->mileage,
                            'car_price' => $car->car_price,
                            'customs' => $car->customs,
                            'price' => $car->price,
                        ];
                    })
                    ->values();
            },
        ]);
    }
}
