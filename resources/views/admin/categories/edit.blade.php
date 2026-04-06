@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.categories.index') }}" class="text-gray-500 hover:text-gray-800"><i class="bi bi-arrow-left"></i></a>
    <h1 class="text-2xl font-bold text-gray-900">Edit: {{ $category->name }}</h1>
</div>

<div class="max-w-xl">
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="space-y-5">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
            <h2 class="font-semibold text-gray-800">Category Details</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $category->slug) }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-brand-red">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                <select name="parent_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                    <option value="">– None –</option>
                    @foreach($categories as $cat)
                        @if($cat->id !== $category->id)
                            <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">{{ old('description', $category->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                <input type="text" name="image_url" value="{{ old('image_url', $category->image_url) }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order) }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-red">
                <span class="text-sm text-gray-700">Active</span>
            </label>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="bi bi-search text-brand-red"></i> SEO Settings
            </h2>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">SEO Title</label>
                <input type="text" name="seo_title" value="{{ old('seo_title', $category->seo_title) }}" maxlength="60"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Meta Description</label>
                <textarea name="seo_description" rows="2" maxlength="160"
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">{{ old('seo_description', $category->seo_description) }}</textarea>
            </div>
        </div>

        <button type="submit" class="w-full bg-brand-red text-white py-2.5 rounded-lg font-medium hover:bg-red-dark transition">
            <i class="bi bi-save me-1"></i> Save Changes
        </button>
    </form>
</div>
@endsection
