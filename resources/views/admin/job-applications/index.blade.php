@extends('layouts.admin')

@section('title', 'Job Applications')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-person-lines-fill text-brand-red"></i> Job Applications
    </h1>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
@endif

<form method="GET" class="mb-4 flex flex-wrap gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, position…" class="border border-gray-200 rounded-lg px-3 py-2 text-sm min-w-[220px]">
    <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach(\App\Models\JobApplication::STATUSES as $key => $label)
            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
    @if(request()->hasAny(['search', 'status']))
        <a href="{{ route('admin.job-applications.index') }}" class="px-4 py-2 rounded-lg text-sm border border-gray-200">Reset</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Applicant</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Position</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Contact</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Submitted</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($applications as $app)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $app->fullName() }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $app->position_applied }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        <div>{{ $app->email }}</div>
                        <div class="text-xs">{{ $app->phone }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ \App\Models\JobApplication::STATUSES[$app->status] ?? $app->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $app->created_at->format('M j, Y H:i') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.job-applications.show', $app) }}" class="text-blue-600 hover:underline text-xs font-medium">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No applications yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $applications->links() }}</div>
@endsection
