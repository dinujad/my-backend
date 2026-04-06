<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryApiController extends Controller
{
    /**
     * GET /api/categories
     * Returns all active categories.
     */
    public function index(): JsonResponse
    {
        $categories = Category::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($c) => $this->format($c));

        return response()->json($categories);
    }

    /**
     * GET /api/categories/{slug}
     * Returns a single active category by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::active()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($this->format($category));
    }

    private function format(Category $c): array
    {
        return [
            'id'              => $c->id,
            'slug'            => $c->slug,
            'name'            => $c->name,
            'description'     => $c->description ?? '',
            'image_url'       => $c->image_url,
            'sort_order'      => $c->sort_order,
            'seo_title'       => $c->seo_title,
            'seo_description' => $c->seo_description,
        ];
    }
}
