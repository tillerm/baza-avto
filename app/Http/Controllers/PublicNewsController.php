<?php

namespace App\Http\Controllers;

use App\Models\News;
use Inertia\Inertia;

class PublicNewsController extends Controller
{
    public function index()
    {
        $news = News::where('approved', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('News/Index', compact('news'));
    }

    public function show(string $id)
    {
        $item = News::where('approved', true)->findOrFail($id);

        return Inertia::render('News/Show', [
            'news' => $item,
        ]);
    }
}
