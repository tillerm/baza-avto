<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CatalogDeliveryCalculatorTest extends TestCase
{
    public function test_it_calculates_delivery_cost_by_route_distance(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/search*' => Http::response([
                [
                    'display_name' => 'Москва, Центральный федеральный округ, Россия',
                    'lat' => '55.7558',
                    'lon' => '37.6173',
                ],
            ]),
            'https://router.project-osrm.org/route/v1/driving/*' => Http::response([
                'routes' => [
                    ['distance' => 1123400],
                ],
            ]),
        ]);

        $response = $this->postJson(route('catalog.calculator.delivery-estimate'), [
            'destination' => 'Москва',
        ]);

        $response->assertOk()->assertJson([
            'origin' => 'Таганрог',
            'destination' => 'Москва, Центральный федеральный округ, Россия',
            'distance_km' => 1124,
            'price' => 78100,
            'is_approximate' => false,
            'pricing' => [
                'base_price' => 100000,
                'price_per_km' => 25,
                'distance_price' => 28100,
            ],
        ]);
    }

    public function test_it_returns_validation_error_when_destination_is_missing(): void
    {
        $response = $this->postJson(route('catalog.calculator.delivery-estimate'), [
            'destination' => '',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('destination');
    }
}
