<?php

namespace App\Services;

use App\Models\Request as CarRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramLeadNotifier
{
    public function sendCarRequest(CarRequest $carRequest): void
    {
        $car = $carRequest->car;
        $carTitle = $car
            ? trim(implode(' ', array_filter([
                $car->supply?->equipment?->model?->brand?->name,
                $car->supply?->equipment?->model?->name,
                $car->release_date ? date('Y', strtotime((string) $car->release_date)) : null,
            ])))
            : null;

        $this->send([
            '<b>Новая заявка по авто</b>',
            'Имя: ' . $this->escape($carRequest->name),
            'Телефон: ' . $this->escape($carRequest->phone),
            'Авто ID: ' . $this->escape((string) $carRequest->car_id),
            $carTitle ? 'Авто: ' . $this->escape($carTitle) : null,
            $car?->state_number ? 'Гос. номер: ' . $this->escape($car->state_number) : null,
            $car ? 'Ссылка: ' . $this->escape(route('catalog.show', ['id' => $car->id])) : null,
        ]);
    }

    public function sendCalculatorRequest(array $payload): void
    {
        $this->send([
            '<b>Новая заявка с главной страницы</b>',
            'Имя: ' . $this->escape($payload['name']),
            'Телефон: ' . $this->escape($payload['phone']),
            'Марка и модель: ' . $this->escape($payload['model']),
            'Пробег: ' . $this->escape($this->resolveConditionLabel($payload['condition'])),
            !empty($payload['year']) ? 'Год: ' . $this->escape($payload['year']) : null,
            !empty($payload['city']) ? 'Город доставки: ' . $this->escape($payload['city']) : null,
            'Согласие на рассылку: ' . (!empty($payload['mailing_agree']) ? 'Да' : 'Нет'),
        ]);
    }

    public function sendDeliveryCalculatorRequest(array $payload): void
    {
        $this->send([
            '<b>New delivery lead</b>',
            'Phone: ' . $this->escape($payload['phone']),
            'Destination: ' . $this->escape($payload['destination']),
            !empty($payload['distance_km']) ? 'Distance: ' . $this->escape((string) $payload['distance_km']) . ' km' : null,
            !empty($payload['price']) ? 'Price: ' . $this->escape((string) $payload['price']) . ' RUB' : null,
            'Mailing consent: ' . (!empty($payload['mailing_agree']) ? 'Yes' : 'No'),
        ]);
    }

    private function send(array $lines): void
    {
        $token = trim((string) config('services.telegram.bot_token'));
        $chatId = trim((string) config('services.telegram.chat_id'));

        if ($token === '' || $chatId === '') {
            Log::warning('Telegram lead notification skipped: missing bot token or chat id.');

            return;
        }

        $text = implode("\n", array_values(array_filter($lines)));

        $response = Http::asForm()
            ->timeout(10)
            ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

        if (!$response->successful()) {
            Log::error('Telegram lead notification failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }

    private function resolveConditionLabel(string $condition): string
    {
        return match ($condition) {
            'used50' => 'С пробегом до 50 тыс. км',
            'used100' => 'До 100 тыс. км',
            'any' => 'Неважно',
            default => $condition,
        };
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
