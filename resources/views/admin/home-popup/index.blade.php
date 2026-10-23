@extends('layouts.admin')

@section('title', 'Home Popup')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
    <i class="bi bi-window-stack text-brand-red"></i> Home Popup
</h1>

<form method="POST" action="{{ route('admin.home-popup.update') }}" enctype="multipart/form-data" class="max-w-2xl">
    @csrf @method('PUT')

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-5">
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="hidden" name="enabled" value="0">
            <input type="checkbox" name="enabled" value="1" class="mt-1 h-4 w-4 accent-red-600" @checked(old('enabled', $popup->enabled ? '1' : '0') == '1')>
            <span>
                <span class="block text-sm font-semibold text-gray-800">Show popup on the home page</span>
                <span class="block text-xs text-gray-500 mt-0.5">Turn this off to disable the popup. It stays off until you enable it again.</span>
            </span>
        </label>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Auto close after (seconds)</label>
            <input type="number" name="close_seconds" min="0" max="120" value="{{ old('close_seconds', $popup->close_seconds) }}"
                   class="w-32 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            <p class="text-xs text-gray-400 mt-1">The visitor can close it sooner. Use 0 to keep it open until they close it. Default is 3.</p>
            @error('close_seconds')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Popup image</label>
            @if($imageUrl)
                <img src="{{ $imageUrl }}" alt="" class="mb-3 max-h-64 rounded-lg border border-gray-100 bg-gray-50 object-contain">
            @else
                <p class="mb-3 text-sm text-gray-500">No custom image yet. The site uses the default poster at <span class="font-mono">/images/popup.png</span>.</p>
            @endif
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                   class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium">
            <p class="text-xs text-gray-400 mt-1">JPG, PNG, or WebP. Max 5 MB.</p>
            @error('image')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
            @if($popup->image_path)
                <label class="mt-3 flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="remove_image" value="1" class="h-4 w-4 accent-red-600">
                    Remove custom image and use the default poster
                </label>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <button type="submit" class="flex items-center gap-2 bg-brand-red text-white px-6 py-2.5 rounded-lg font-medium hover:bg-red-dark transition">
            <i class="bi bi-save"></i> Save popup
        </button>
    </div>
</form>
@endsection
