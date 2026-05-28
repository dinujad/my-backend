<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomePromoBanner;
use App\Support\ProductMediaPath;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomePromoBannerController extends Controller
{
    public function index(): View
    {
        $banners = HomePromoBanner::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.promo-banners.index', compact('banners'));
    }

    public function create(): View
    {
        $banner = new HomePromoBanner([
            'action_text' => 'Shop now',
            'href' => '/products',
            'sort_order' => (HomePromoBanner::max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        return view('admin.promo-banners.create', compact('banner'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['has_discount'] = $request->boolean('has_discount');

        if ($request->hasFile('image')) {
            $data['image'] = 'storage/'.ProductMediaPath::storeUpload($request->file('image'), 'promo-banners');
        }

        HomePromoBanner::create($data);

        return redirect()->route('admin.promo-banners.index')->with('success', 'Promo banner created.');
    }

    public function edit(HomePromoBanner $promo_banner): View
    {
        return view('admin.promo-banners.edit', ['banner' => $promo_banner]);
    }

    public function update(Request $request, HomePromoBanner $promo_banner): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['has_discount'] = $request->boolean('has_discount');

        if ($request->hasFile('image')) {
            $promo_banner->deleteImageFile();
            $data['image'] = 'storage/'.ProductMediaPath::storeUpload($request->file('image'), 'promo-banners');
        }

        $promo_banner->update($data);

        return redirect()->route('admin.promo-banners.index')->with('success', 'Promo banner updated.');
    }

    public function destroy(HomePromoBanner $promo_banner): RedirectResponse
    {
        $promo_banner->deleteImageFile();
        $promo_banner->delete();

        return redirect()->route('admin.promo-banners.index')->with('success', 'Promo banner deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:120',
            'bold_text' => 'nullable|string|max:120',
            'post_text' => 'nullable|string|max:80',
            'second_line' => 'nullable|string|max:120',
            'has_discount' => 'boolean',
            'discount_number' => 'nullable|string|max:10',
            'action_text' => 'nullable|string|max:80',
            'href' => 'required|string|max:500',
            'image_alt' => 'nullable|string|max:200',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:5120',
        ]);
    }
}
