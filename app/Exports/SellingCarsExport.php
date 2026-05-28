<?php

namespace App\Exports;

use App\Enums\Body;
use App\Enums\Fuel;
use App\Models\Car;
use Carbon\Carbon;

class SellingCarsExport extends Export
{
    public function query()
    {
        return Car::with('supply.equipment.engine')->where('status', '=', 'SELLING');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Гос. номер',
            'Комплектация',
            'Двигатель',
            'Цена',
            'Цвет',
            'Пробег',
            'Год',
            'Кузов',
            'Двери',
            'Сидения',
            'Макс. скорость',
            'Разгон до 100км/ч',
            'Расход при городском цикле',
            'Расход при загородном цикле',
            'Расход при смешанном цикле',
            'Двигатель',
            'Объем',
            'Цилиндры',
            'Макс. мощность',
            'Макс. крутящий момент',
            'Топливо',
        ];
    }

    public function map($car): array
    {
        return [
            $car->id,
            $car->state_number,
            $car->supply->equipment->name,
            $car->supply->equipment->engine->name,
            number_format($car->price, 2, ',', ' ') . '₽',
            $car->color,
            $car->mileage ? $car->mileage . ' км' : '',
            $car->release_date ? Carbon::parse($car->release_date)->year : '',
            Body::array()[$car->supply->equipment->body],
            $car->supply->equipment->doors_count,
            $car->supply->equipment->seats_count,
            $car->supply->equipment->engine->name,
            $car->supply->equipment->engine->capacity . ' см3',
            Fuel::array()[$car->supply->equipment->engine->fuel],
        ];
    }
}
