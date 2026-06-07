@extends('layouts.admin')

@section('title', 'Job Openings')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i class="bi bi-briefcase text-brand-red"></i> Job Openings
        </h1>
        <p class="text-sm text-gray-500 mt-1">Manage careers page roles shown on printworks.lk/career</p>
    </div>
    <a href="{{ route('admin.job-openings.create') }}" class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-dark transition text-sm font-medium">
        <i class="bi bi-plus-lg"></i> Add opening
    </a>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Title</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Location</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Order</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($openings as $opening)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $opening->title }}</p>
                        @if($opening->employment_type)<p class="text-xs text-gray-500">{{ $opening->employment_type }}</p>@endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $opening->location }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $opening->sort_order }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($opening->is_active)
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Hidden</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.job-openings.edit', $opening) }}" class="text-blue-600 hover:underline text-xs font-medium mr-3">Edit</a>
                        <form method="POST" action="{{ route('admin.job-openings.destroy', $opening) }}" class="inline" onsubmit="return confirm('Delete this job opening?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:underline text-xs font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                        No job openings yet.
                        <a href="{{ route('admin.job-openings.create') }}" class="text-brand-red font-medium hover:underline">Add your first role</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
