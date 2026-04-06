@extends('layouts.admin')

@section('title', 'New Blog Post')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.blog.index') }}" class="text-gray-500 hover:text-gray-800"><i class="bi bi-arrow-left"></i></a>
    <h1 class="text-2xl font-bold text-gray-900">New Blog Post</h1>
</div>

<form method="POST" action="{{ route('admin.blog.store') }}">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input name="title" value="{{ old('title') }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input name="slug" value="{{ old('slug') }}" placeholder="auto-generated"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                    <textarea name="excerpt" rows="2" placeholder="Short description..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">{{ old('excerpt') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="14" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red font-mono">{{ old('content') }}</textarea>
                </div>
            </div>

            {{-- SEO --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="bi bi-search text-brand-red"></i> SEO Settings
                </h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">SEO Title <span class="text-gray-400">(max 60 chars)</span></label>
                        <input name="seo_title" value="{{ old('seo_title') }}" maxlength="60"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">SEO Description <span class="text-gray-400">(max 160 chars)</span></label>
                        <textarea name="seo_description" rows="2" maxlength="160" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">{{ old('seo_description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                <h2 class="font-semibold text-gray-800">Publish</h2>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }} class="rounded border-gray-300 text-brand-red">
                    <span class="text-sm text-gray-700">Publish immediately</span>
                </label>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
                <h2 class="font-semibold text-gray-800">Category</h2>
                <select name="blog_category_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                    <option value="">Uncategorised</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('blog_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Featured Image URL</label>
                    <input name="featured_image" value="{{ old('featured_image') }}" placeholder="https://..."
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                </div>
            </div>
            <button type="submit" class="w-full bg-brand-red text-white py-2.5 rounded-lg font-medium hover:bg-red-dark transition">
                Publish Post
            </button>
        </div>
    </div>
</form>
@endsection
