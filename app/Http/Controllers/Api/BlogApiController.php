<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $posts = $this->published()
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->input('category'))))
            ->get()
            ->map(fn ($p) => $this->format($p, false));

        return response()->json($posts);
    }

    public function show(string $slug): JsonResponse
    {
        $post = $this->published()->where('slug', $slug)->first();

        if (! $post) {
            abort(404);
        }

        $related = $this->published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->reorder()
                ->orderByRaw('CASE WHEN blog_category_id = ? THEN 0 ELSE 1 END', [$post->blog_category_id])
                ->orderByDesc('published_at'))
            ->limit(3)
            ->get()
            ->map(fn ($p) => $this->format($p, false));

        return response()->json($this->format($post, true) + ['relatedPosts' => $related]);
    }

    private function published(): Builder
    {
        return BlogPost::with('category', 'author')
            ->where('is_published', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');
    }

    private function format(BlogPost $p, bool $withContent): array
    {
        $published = $p->published_at ?? $p->created_at;

        $data = [
            'slug' => $p->slug,
            'title' => $p->title,
            'description' => $p->seo_description ?: $p->excerpt,
            'excerpt' => $p->excerpt,
            'content' => $withContent ? $p->content : null,
            'featured_image' => $p->featured_image,
            'featured_image_alt' => $p->featured_image_alt ?: $p->title,
            'datePublished' => $published?->format('Y-m-d'),
            'publishedAt' => $published?->toIso8601String(),
            'dateModified' => $p->updated_at?->toIso8601String(),
            'readingTime' => $p->reading_time,
            'wordCount' => $p->word_count,
            'seo_title' => $p->seo_title,
            'seo_description' => $p->seo_description,
            'focus_keyword' => $p->focus_keyword,
            'meta_keywords' => $p->meta_keywords,
            'canonical_url' => $p->canonical_url,
            'og_image' => $p->og_image,
            'noindex' => (bool) $p->noindex,
            'tags' => $p->tags ?? [],
            'faqs' => $p->faqs ?? [],
            'relatedLinks' => $p->related_links ?? [],
            'author' => $p->author?->name,
            'category' => $p->category ? ['name' => $p->category->name, 'slug' => $p->category->slug] : null,
        ];

        if (! $withContent) {
            unset($data['content']);
        }

        return $data;
    }
}
