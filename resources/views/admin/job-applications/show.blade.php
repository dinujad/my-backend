@extends('layouts.admin')

@section('title', 'Application — ' . $application->fullName())

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="{{ route('admin.job-applications.index') }}" class="text-sm text-brand-red hover:underline">← Back to applications</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $application->fullName() }}</h1>
        <p class="text-sm text-gray-500">{{ $application->position_applied }}</p>
    </div>
    <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100">{{ \App\Models\JobApplication::STATUSES[$application->status] ?? $application->status }}</span>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <h2 class="font-bold text-gray-900 mb-4">Contact details</h2>
            <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">Email</dt><dd class="font-medium">{{ $application->email }}</dd></div>
                <div><dt class="text-gray-500">Phone</dt><dd class="font-medium">{{ $application->phone }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd class="font-medium">{{ $application->street_address }}@if($application->address_line_2), {{ $application->address_line_2 }}@endif</dd></div>
            </dl>
        </div>

        @if($application->employment_history)
            <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
                <h2 class="font-bold text-gray-900 mb-4">Previous employment</h2>
                <ul class="space-y-3 text-sm">
                    @foreach($application->employment_history as $row)
                        <li class="border border-gray-100 rounded-lg p-3">
                            <p class="font-semibold">{{ $row['employer'] ?? '—' }}</p>
                            <p class="text-gray-600">{{ $row['position'] ?? '' }} @if(!empty($row['dates'])) · {{ $row['dates'] }}@endif</p>
                            @if(!empty($row['phone']))<p class="text-xs text-gray-500">{{ $row['phone'] }}</p>@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($application->cover_letter)
            <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
                <h2 class="font-bold text-gray-900 mb-4">Cover letter</h2>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $application->cover_letter }}</p>
            </div>
        @endif

        @if($application->resume_path)
            <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
                <h2 class="font-bold text-gray-900 mb-4">Resume</h2>
                <a href="{{ $application->resumeUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-brand-red font-medium text-sm hover:underline">
                    <i class="bi bi-file-earmark-pdf"></i> Download resume
                </a>
            </div>
        @endif
    </div>

    <div>
        <form method="POST" action="{{ route('admin.job-applications.status', $application) }}" class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm space-y-4 sticky top-6">
            @csrf
            <h2 class="font-bold text-gray-900">Update status</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    @foreach(\App\Models\JobApplication::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected($application->status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Admin notes</label>
                <textarea name="admin_notes" rows="5" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">{{ old('admin_notes', $application->admin_notes) }}</textarea>
            </div>
            <button type="submit" class="w-full bg-brand-red text-white py-2.5 rounded-lg text-sm font-medium hover:bg-red-dark">Save</button>
        </form>
    </div>
</div>
@endsection
