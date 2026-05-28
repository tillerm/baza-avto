<?php

namespace App\Http\Requests;

use App\Enums\Body;
use App\Enums\Type;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EquipmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|max:255',
            'type' => 'required|in:'.implode(',', Type::names()),
            'doors_count' => 'required|integer|max:255',
            'seats_count' => 'required|integer|max:255',
            'body' => 'required|in:'.implode(',', Body::names()),
            'model_id' => 'required|integer|exists:models,id',
            'engine_id' => 'required|integer|exists:engines,id',
        ];

        if (Schema::hasColumn('equipments', 'generation_id')) {
            $rules['generation_id'] = ['nullable', 'integer'];
            if (Schema::hasTable('generations')) {
                $rules['generation_id'][] = 'exists:generations,id';
            }
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if (!Schema::hasColumn('equipments', 'generation_id')) {
            return;
        }

        if ($this->filled('generation_id')) {
            return;
        }

        $modelId = $this->input('model_id');
        if (!$modelId) {
            return;
        }

        if (!Schema::hasTable('generations')) {
            return;
        }

        $generationId = DB::table('generations')
            ->where('model_id', $modelId)
            ->orderBy('to')
            ->orderByDesc('from')
            ->orderByDesc('restyling')
            ->orderByDesc('id')
            ->value('id');

        if ($generationId) {
            $this->merge(['generation_id' => $generationId]);
        }
    }
}
