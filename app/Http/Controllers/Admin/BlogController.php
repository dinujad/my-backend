<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Media;
use App\Support\ProductMediaPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $query = BlogPost::with('category', 'author')->latest();

        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->input('q').'%');
        }
        if ($request->input('status') === 'published') {
            $query->where('is_published', true);
        } elseif ($request->input('status') === 'draft') {
            $query->where('is_published', false);
        }

        $posts = $query->paginate(15)->withQueryString();

        return view('admin.blog.index', compact('posts'));
    }

    public function create(): View
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blog.create', ['categories' => $categories, 'blog' => new BlogPost]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = auth()->id();

        $post = BlogPost::create($data);

        return redirect()->route('admin.blog.edit', $post)->with('success', 'Blog post created.');
    }

    public function edit(BlogPost $blog): View
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blog.edit', compact('blog', 'categories'));
    }

    public function update(Request $request, BlogPost $blog): RedirectResponse
    {
        $blog->update($this->validated($request, $blog));

        return redirect()->route('admin.blog.edit', $blog)->with('success', 'Blog post updated.');
    }

    public function destroy(BlogPost $blog): RedirectResponse
    {
        $blog->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted.');
    }

    /** Image upload for the rich-text editor. Responds in TinyMCE's {location} format. */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        return response()->json(['location' => $this->storeImage($request->file('file'), $request->input('alt'))]);
    }

    /** Quick-add a blog category from the post form. */
    public function storeCategory(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:blog_categories,name']);
        $category = BlogCategory::create(['name' => $data['name']]);

        return response()->json(['id' => $category->id, 'name' => $category->name]);
    }

    private function validated(Request $request, ?BlogPost $blog = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:500',
            'featured_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'featured_image_alt' => 'nullable|string|max:255',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:70',
            'seo_description' => 'nullable|string|max:170',
            'focus_keyword' => 'nullable|string|max:100',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url|max:500',
            'og_image' => 'nullable|string|max:500',
            'noindex' => 'boolean',
            'tags_text' => 'nullable|string|max:500',
            'faqs' => 'nullable|array|max:10',
            'faqs.*.question' => 'nullable|string|max:255',
            'faqs.*.answer' => 'nullable|string|max:1000',
            'related_links_text' => 'nullable|string',
        ]);

        // Slug: always normalised and unique, from the given slug or the title.
        $data['slug'] = BlogPost::uniqueSlug(filled($data['slug'] ?? null) ? $data['slug'] : $data['title'], $blog?->id);

        if ($request->hasFile('featured_image_file')) {
            $data['featured_image'] = $this->storeImage($request->file('featured_image_file'), $data['featured_image_alt'] ?? $data['title']);
        }
        unset($data['featured_image_file']);

        $data['is_published'] = $request->boolean('is_published');
        $data['noindex'] = $request->boolean('noindex');

        if (filled($data['published_at'] ?? null)) {
            $data['published_at'] = Carbon::parse($data['published_at']);
        } elseif ($data['is_published']) {
            $data['published_at'] = $blog?->published_at ?? now();
        } else {
            $data['published_at'] = $blog?->published_at;
        }

        $data['tags'] = collect(explode(',', (string) ($data['tags_text'] ?? '')))
            ->map(fn ($t) => trim($t))->filter()->unique()->values()->all();
        unset($data['tags_text']);

        $data['related_links'] = collect(explode("\n", (string) $request->input('related_links_text', '')))
            ->map(function ($line) {
                $parts = explode('|', $line, 2);
                $label = trim($parts[0] ?? '');
                $href = trim($parts[1] ?? '');

                return $label !== '' && $href !== '' ? ['label' => $label, 'href' => $href] : null;
            })->filter()->values()->all();
        unset($data['related_links_text']);

        $data['faqs'] = collect($data['faqs'] ?? [])
            ->filter(fn ($f) => filled($f['question'] ?? null) && filled($f['answer'] ?? null))
            ->map(fn ($f) => ['question' => trim($f['question']), 'answer' => trim($f['answer'])])
            ->values()->all();

        return $data;
    }

    /** Store an image on the upload disk, register it in the media library, return its public URL. */
    private function storeImage(UploadedFile $file, ?string $alt = null): string
    {
        $path = ProductMediaPath::storeUpload($file, 'blog');
        $dimensions = @getimagesize($file->getRealPath());

        $media = Media::create([
            'user_id' => auth()->id(),
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'alt_text' => $alt ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions ? $dimensions[0] : null,
            'height' => $dimensions ? $dimensions[1] : null,
            'folder' => '/blog',
        ]);

        return $media->url;
    }
}
