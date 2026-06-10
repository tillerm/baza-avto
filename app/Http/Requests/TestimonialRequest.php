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

    protected function prepareForValidation(): void
    {
        $testimonial = $this->route('testimonial');

        if (!$testimonial instanceof Testimonial) {
            return;
        }

        $this->merge([
            'type' => $this->has('type') ? $this->input('type') : $testimonial->type,
            'title' => $this->has('title') ? $this->input('title') : $testimonial->title,
            'text' => $this->has('text') ? $this->input('text') : $testimonial->text,
            'author_name' => $this->has('author_name') ? $this->input('author_name') : $testimonial->author_name,
            'car_model' => $this->has('car_model') ? $this->input('car_model') : $testimonial->car_model,
            'city' => $this->has('city') ? $this->input('city') : $testimonial->city,
            'rating' => $this->has('rating') ? $this->input('rating') : $testimonial->rating,
            'video_url' => $this->has('video_url') ? $this->input('video_url') : $testimonial->video_url,
            'position' => $this->has('position') ? $this->input('position') : $testimonial->position,
            'is_active' => $this->has('is_active') ? $this->input('is_active') : $testimonial->is_active,
        ]);
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
            'photo' => ['nullable', 'image', 'max:5120'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
