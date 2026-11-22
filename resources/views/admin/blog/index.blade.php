@extends('layouts.admin')

@section('title', 'Blog Posts')

@section('content')
@php $frontendUrl = rtrim((string) config('app.frontend_url', 'https://printworks.lk'), '/'); @endphp
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-journal-text text-brand-red"></i> Blog Posts
    </h1>
    <a href="{{ route('admin.blog.create') }}" class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-dark transition text-sm font-medium">
        <i class="bi bi-plus-lg"></i> New Post
    </a>
</div>

<form method="GET" class="flex flex-wrap gap-2 mb-4">
    <input name="q" value="{{ request('q') }}" placeholder="Search titles..."
           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red bg-white">
    <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
        <option value="">All</option>
        <option value="published" @selected(request('status') === 'published')>Published</option>
        <option value="draft" @selected(request('status') === 'draft')>Drafts</option>
    </select>
    <button class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm">Filter</button>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Post</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Category</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">SEO</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($posts as $post)
                @php
                    $seoOk = collect([
                        filled($post->focus_keyword),
                        filled($post->seo_title),
                        strlen((string) $post->seo_description) >= 120,
                        filled($post->featured_image) && filled($post->featured_image_alt),
                        $post->word_count >= 600,
                    ])->filter()->count();
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-16 h-10 rounded bg-gray-100 overflow-hidden flex-shrink-0">
                                @if($post->featured_image)
                                    <img src="{{ $post->featured_image }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900">{{ Str::limit($post->title, 60) }}</p>
                                <p class="text-xs text-gray-400 font-mono truncate">/blog/{{ $post->slug }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $post->category?->name ?? '–' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $seoOk >= 5 ? 'bg-green-100 text-green-700' : ($seoOk >= 3 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}"
                              title="Focus keyword, SEO title, meta description, featured image + alt, 600+ words">{{ $seoOk }}/5</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($post->is_published)
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Published</span>
                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $post->published_at?->format('d M Y') }}</p>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Draft</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-3">
                            @if($post->is_published)
                                <a href="{{ $frontendUrl }}/blog/{{ $post->slug }}" target="_blank" rel="noopener" class="text-gray-500 hover:text-gray-800 text-xs">View</a>
                            @endif
                            <a href="{{ route('admin.blog.edit', $post) }}" class="text-blue-500 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No blog posts yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($posts->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
