<?php

namespace App\Http\Controllers;

use App\Services\TelegramLeadNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeadController extends Controller
{
    public function storeCalculator(Request $request, TelegramLeadNotifier $telegramLeadNotifier): JsonResponse
    {
        $validated = $request->validate([
            'model' => ['required', 'string', 'max:255'],
            'condition' => ['required', Rule::in(['used50', 'used100', 'any'])],
            'year' => ['nullable', 'string', 'max:4'],
            'city' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'agree' => ['accepted'],
            'mailing_agree' => ['nullable', 'boolean'],
        ]);

        $phoneDigits = preg_replace('/\D+/', '', $validated['phone']);

        if (strlen((string) $phoneDigits) !== 11) {
            throw ValidationException::withMessages([
                'phone' => 'Укажите телефон в формате +7 (999) 999-99-99.',
            ]);
        }

        $telegramLeadNotifier->sendCalculatorRequest([
            ...$validated,
            'phone' => $validated['phone'],
            'mailing_agree' => (bool) ($validated['mailing_agree'] ?? false),
        ]);

        return response()->json([
            'message' => 'Заявка отправлена.',
        ]);
    }
    public function storeDeliveryCalculator(Request $request, TelegramLeadNotifier $telegramLeadNotifier): JsonResponse
    {
        $validated = $request->validate([
            'destination' => ['required', 'string', 'max:255'],
            'mailing_agree' => ['nullable', 'boolean'],
            'distance_km' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'integer', 'min:1'],
        ]);

        // $telegramLeadNotifier->sendDeliveryCalculatorRequest([
        //     'destination' => $validated['destination'],
        //     'mailing_agree' => (bool) ($validated['mailing_agree'] ?? false),
        //     'distance_km' => $validated['distance_km'] ?? null,
        //     'price' => $validated['price'] ?? null,
        // ]);

        return response()->json([
            'message' => 'Заявка отправлена.',
        ]);
    }
}
