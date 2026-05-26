@extends('layouts.admin')

@section('title', 'Home Promo Banners')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i class="bi bi-grid-3x3-gap text-brand-red"></i> Home Promo Banners
        </h1>
        <p class="text-sm text-gray-500 mt-1">Manage the four promotional cards below the hero slider.</p>
    </div>
    <a href="{{ route('admin.promo-banners.create') }}" class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-dark transition text-sm font-medium">
        <i class="bi bi-plus-lg"></i> Add banner
    </a>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Image</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Content</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Order</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($banners as $banner)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 w-28">
                        @if($banner->image)
                            <img src="{{ $banner->imageUrl() }}" alt="" class="h-16 w-20 rounded-lg object-contain border border-gray-100 bg-gray-50">
                        @else
                            <span class="text-xs text-gray-400">No image</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $banner->title }}</p>
                        @if($banner->bold_text)<p class="text-xs text-gray-700">{{ $banner->bold_text }} @if($banner->second_line)· {{ $banner->second_line }}@endif</p>@endif
                        @if($banner->has_discount)<p class="text-xs text-brand-red font-bold mt-0.5">UP TO {{ $banner->discount_number }}%</p>@endif
                        <p class="text-xs text-gray-400 mt-1 truncate max-w-xs">{{ $banner->href }}</p>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $banner->sort_order }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($banner->is_active)
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Hidden</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.promo-banners.edit', $banner) }}" class="text-blue-600 hover:underline text-xs font-medium mr-3">Edit</a>
                        <form method="POST" action="{{ route('admin.promo-banners.destroy', $banner) }}" class="inline" onsubmit="return confirm('Delete this banner?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline text-xs font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                        No banners yet.
                        <a href="{{ route('admin.promo-banners.create') }}" class="text-brand-red font-medium hover:underline">Add your first banner</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
