<?php

namespace App\Http\Controllers;

use App\Models\CarPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CarPhotoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'photo' => 'nullable|required_without:photos|base64mimes:jpg,png,jpeg,gif|base64max:2048',
            'photos' => 'nullable|required_without:photo|array|min:1',
            'photos.*' => 'required|base64mimes:jpg,png,jpeg,gif|base64max:2048',
        ]);

        $photos = isset($validated['photos']) ? $validated['photos'] : [$validated['photo']];

        foreach ($photos as $photo) {
            $fileName = Str::uuid().'.'.$this->extractExtension($photo);
            $image = base64_decode(explode(',', $photo)[1]);
            Storage::disk('public')->put($fileName, $image);

            $isFirstPhoto = ! CarPhoto::where('car_id', $validated['car_id'])->exists();

            CarPhoto::create([
                'car_id' => $validated['car_id'],
                'photo' => $fileName,
                'is_primary' => $isFirstPhoto,
            ]);
        }

        return redirect()->back();
    }

    private function extractExtension(string $photo): string
    {
        $mime = explode(';', explode(',', $photo)[0])[0];

        return match ($mime) {
            'data:image/png' => 'png',
            'data:image/gif' => 'gif',
            default => 'jpg',
        };
    }

    public function delete(string $id)
    {
        $photo = CarPhoto::findOrFail($id);
        $carId = $photo->car_id;
        $wasPrimary = $photo->is_primary;

        Storage::disk('public')->delete($photo->photo);
        $photo->delete();

        if ($wasPrimary) {
            CarPhoto::where('car_id', $carId)
                ->orderBy('id')
                ->limit(1)
                ->update(['is_primary' => true]);
        }

        return redirect()->back();
    }

    public function makePrimary(string $id)
    {
        $photo = CarPhoto::findOrFail($id);

        CarPhoto::where('car_id', $photo->car_id)->update(['is_primary' => false]);
        $photo->update(['is_primary' => true]);

        return redirect()->back();
    }
}
