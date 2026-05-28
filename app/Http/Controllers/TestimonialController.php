<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestimonialRequest;
use App\Models\Testimonial;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    public function index(): Response
    {
        $testimonials = Testimonial::orderByDesc('position')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('CRM/Testimonials/Index', [
            'testimonials' => $testimonials,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CRM/Testimonials/Edit', [
            'testimonial' => [
                'type' => Testimonial::TYPE_TEXT,
                'title' => '',
                'text' => '',
                'author_name' => '',
                'car_model' => '',
                'city' => '',
                'rating' => 5,
                'video_url' => '',
                'position' => 0,
                'is_active' => true,
            ],
            'isNew' => true,
        ]);
    }

    public function store(TestimonialRequest $request)
    {
        Testimonial::create($request->validated());

        return redirect()->route('crm.testimonials.index');
    }

    public function edit(Testimonial $testimonial): Response
    {
        return Inertia::render('CRM/Testimonials/Edit', [
            'testimonial' => $testimonial,
            'isNew' => false,
        ]);
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial)
    {
        $testimonial->update($request->validated());

        return redirect()->route('crm.testimonials.index');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('crm.testimonials.index');
    }
}
