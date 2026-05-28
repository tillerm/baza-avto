<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsSource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::orderByDesc('published_at')->orderByDesc('id')->get();

        return Inertia::render('CRM/News/Index', compact('news'));
    }

    public function create()
    {
        return Inertia::render('CRM/News/Create');
    }

    public function edit(string $id)
    {
        $news = News::findOrFail($id);

        return Inertia::render('CRM/News/Edit', compact('news'));
    }

    public function sources()
    {
        $sources = NewsSource::orderByDesc('id')->get();

        return Inertia::render('CRM/News/Sources', compact('sources'));
    }

    public function storeSource(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
        ]);
        NewsSource::create($data);

        return redirect()->route('crm.news.sources.index');
    }

    public function toggleSource(string $id)
    {
        $source = NewsSource::findOrFail($id);
        $source->active = !$source->active;
        $source->save();

        return redirect()->route('crm.news.sources.index');
    }

    public function storeNews(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'published_at' => 'nullable|date',
        ]);
        News::create($data);

        return redirect()->route('crm.news.create');
    }

    public function update(Request $request, string $id)
    {
        $news = News::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'published_at' => 'nullable|date',
            'approved' => 'sometimes|boolean',
        ]);

        $news->update($data);

        return redirect()->route('crm.news.index');
    }

    public function approve(string $id)
    {
        $news = News::findOrFail($id);
        $news->approved = true;
        $news->save();

        return redirect()->route('crm.news.index');
    }

    public function destroy(string $id)
    {
        $news = News::findOrFail($id);
        $news->delete();

        return redirect()->route('crm.news.index');
    }
}
