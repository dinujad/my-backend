<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::with('category', 'author')->latest()->paginate(15);

        return view('admin.blog.index', compact('posts'));
    }

    public function create(): View
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blog.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:500',
            'is_published' => 'boolean',
            'seo_title' => 'nullable|string|max:60',
            'faqs' => 'nullable|array|max:10',
            'faqs.*.question' => 'required_with:faqs|string|max:255',
            'faqs.*.answer' => 'required_with:faqs|string|max:1000',
            'related_links_text' => 'nullable|string',
            'seo_description' => 'nullable|string|max:160',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['is_published'] = $request->boolean('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;
        $data['user_id'] = auth()->id();

        $data['related_links'] = collect(explode("\n", (string) $request->input('related_links_text', '')))->map(function ($line) { $parts = explode('|', $line, 2); $label = trim($parts[0] ?? ''); $href = trim($parts[1] ?? ''); return $label !== '' && $href !== '' ? ['label' => $label, 'href' => $href] : null; })->filter()->values()->all();
        if (isset($data['faqs'])) {
            $data['faqs'] = collect($data['faqs'] ?? [])->filter(fn ($f) => filled($f['question'] ?? null) && filled($f['answer'] ?? null))->values()->all();
        }

        BlogPost::create($data);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created.');
    }

    public function edit(BlogPost $blog): View
    {
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blog.edit', compact('blog', 'categories'));
    }

    public function update(Request $request, BlogPost $blog): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $blog->id,
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:500',
            'is_published' => 'boolean',
            'seo_title' => 'nullable|string|max:60',
            'faqs' => 'nullable|array|max:10',
            'faqs.*.question' => 'required_with:faqs|string|max:255',
            'faqs.*.answer' => 'required_with:faqs|string|max:1000',
            'related_links_text' => 'nullable|string',
            'seo_description' => 'nullable|string|max:160',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['is_published'] = $request->boolean('is_published');
        if ($data['is_published'] && !$blog->published_at) {
            $data['published_at'] = now();
        }

        $data['related_links'] = collect(explode("\n", (string) $request->input('related_links_text', '')))->map(function ($line) { $parts = explode('|', $line, 2); $label = trim($parts[0] ?? ''); $href = trim($parts[1] ?? ''); return $label !== '' && $href !== '' ? ['label' => $label, 'href' => $href] : null; })->filter()->values()->all();
        if (isset($data['faqs'])) {
            $data['faqs'] = collect($data['faqs'] ?? [])->filter(fn ($f) => filled($f['question'] ?? null) && filled($f['answer'] ?? null))->values()->all();
        }

        $blog->update($data);

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated.');
    }

    public function destroy(BlogPost $blog): RedirectResponse
    {
        $blog->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted.');
    }
}
