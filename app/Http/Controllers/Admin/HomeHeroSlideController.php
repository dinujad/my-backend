<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeHeroSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeHeroSlideController extends Controller
{
    public function index(): View
    {
        $slides = HomeHeroSlide::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.hero-slides.index', compact('slides'));
    }

    public function create(): View
    {
        $slide = new HomeHeroSlide([
            'cta_label' => 'Start Buying',
            'cta_url' => '/products',
            'sort_order' => (HomeHeroSlide::max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        return view('admin.hero-slides.create', compact('slide'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $data['image'] = 'storage/'.$request->file('image')->store('hero-slides', 'public');
        }

        HomeHeroSlide::create($data);

        return redirect()->route('admin.hero-slides.index')->with('success', 'Hero slide created.');
    }

    public function edit(HomeHeroSlide $hero_slide): View
    {
        return view('admin.hero-slides.edit', ['slide' => $hero_slide]);
    }

    public function update(Request $request, HomeHeroSlide $hero_slide): RedirectResponse
    {
        $data = $this->validated($request, $hero_slide->id);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $hero_slide->deleteImageFile();
            $data['image'] = 'storage/'.$request->file('image')->store('hero-slides', 'public');
        }

        $hero_slide->update($data);

        return redirect()->route('admin.hero-slides.index')->with('success', 'Hero slide updated.');
    }

    public function destroy(HomeHeroSlide $hero_slide): RedirectResponse
    {
        $hero_slide->deleteImageFile();
        $hero_slide->delete();

        return redirect()->route('admin.hero-slides.index')->with('success', 'Hero slide deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'eyebrow' => 'nullable|string|max:120',
            'title_line1' => 'required|string|max:200',
            'title_line2' => 'nullable|string|max:200',
            'highlight_text' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:1000',
            'cta_label' => 'required|string|max:80',
            'cta_url' => 'required|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:5120',
        ]);
    }
}
