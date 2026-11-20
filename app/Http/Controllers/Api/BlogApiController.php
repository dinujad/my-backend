<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;

class BlogApiController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = BlogPost::with('category')
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($p) => $this->format($p));

        return response()->json($posts);
    }

    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::with('category')
            ->where('is_published', true)
            ->where('slug', $slug)
            ->first();

        if (! $post) {
            abort(404);
        }

        return response()->json($this->format($post));
    }

    private function format(BlogPost $p): array
    {
        return [
            'slug' => $p->slug,
            'title' => $p->title,
            'description' => $p->seo_description ?: $p->excerpt,
            'excerpt' => $p->excerpt,
            'content' => $p->content,
            'featured_image' => $p->featured_image,
            'datePublished' => $p->published_at ? $p->published_at->format('Y-m-d') : $p->created_at->format('Y-m-d'),
            'seo_title' => $p->seo_title,
            'seo_description' => $p->seo_description,
            'faqs' => $p->faqs ?? [],
            'relatedLinks' => $p->related_links ?? [],
            'category' => $p->category ? ['name' => $p->category->name, 'slug' => $p->category->slug] : null,
        ];
    }
}
