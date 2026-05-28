<?php

namespace App\Enums;

enum Body: string
{
    use EnumToArray;

    case SUV = 'Внедорожник';
    case JEEP = 'Джип';
    case CONVERTIBLE = 'Кабриолет';
    case CROSSOVER = 'Кроссовер';
    case MINIVAN = 'Минивэн';
    case COUPE = 'Купе';
    case LIMOUSINE = 'Лимузин';
    case MINI_VAN = 'Мини-фургон';
    case PICKUP_TRUCK = 'Пикап';
    case ROADSTER = 'Родстер';
    case SEDAN = 'Седан';
    case STATION_WAGON = 'Универсал';
    case VAN = 'Фургон';
    case HATCHBACK = 'Хэтчбек';
    case OTHER = 'Другой';
}
