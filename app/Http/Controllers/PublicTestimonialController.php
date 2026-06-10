<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Inertia\Inertia;

class PublicTestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get(['id', 'type', 'title', 'text', 'author_name', 'car_model', 'city', 'rating', 'video_url', 'photo']);

        return Inertia::render('Testimonials', [
            'videoTestimonials' => $testimonials->where('type', Testimonial::TYPE_VIDEO)->values(),
            'textTestimonials' => $testimonials->where('type', Testimonial::TYPE_TEXT)->values(),
        ]);
    }
}
