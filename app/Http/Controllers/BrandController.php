<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $brands = Brand::search($request->search, [
            '' => ['id', 'name'],
            'country' => ['name'],
        ])
            ->with('country')
            ->orderByDesc('id')
            ->get();
        $countries = Country::orderBy('name')->get();

        return Inertia::render('CRM/Brands/Index', compact('brands', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:brands',
            'country_id' => 'required|integer|exists:countries,id',
            'logo' => 'nullable|image|max:2048',
        ]);
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create($validated);

        $redirect = redirect()->back();
        if ($request->boolean('wizard')) {
            $redirect->with('wizard_created_brand_id', $brand->id);
        }

        return $redirect;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::with('country')->findOrFail($id);
        $countries = Country::orderBy('name')->get();

        return Inertia::render('CRM/Brands/Show', compact('brand', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|max:255|unique:brands,name,'.$brand->id,
            'country_id' => 'required|integer|exists:countries,id',
            'logo' => 'nullable|image|max:2048',
        ]);
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('brands', 'public');
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $validated['logo'] = $logoPath;
        }
        $brand->update($validated);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return redirect()->route('crm.brands.index');
    }
}
