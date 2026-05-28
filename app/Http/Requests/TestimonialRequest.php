<?php

namespace App\Http\Requests;

use App\Models\Testimonial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([Testimonial::TYPE_TEXT, Testimonial::TYPE_VIDEO])],
            'title' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => $this->input('type') === Testimonial::TYPE_VIDEO)],
            'video_url' => ['nullable', 'url', 'max:255', Rule::requiredIf(fn () => $this->input('type') === Testimonial::TYPE_VIDEO)],
            'text' => ['nullable', 'string', 'max:5000', Rule::requiredIf(fn () => $this->input('type') === Testimonial::TYPE_TEXT)],
            'author_name' => ['nullable', 'string', 'max:120', Rule::requiredIf(fn () => $this->input('type') === Testimonial::TYPE_TEXT)],
            'car_model' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
