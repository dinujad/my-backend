@extends('layouts.admin')

@section('title', 'Add Job Opening')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Add Job Opening</h1>
</div>

<form method="POST" action="{{ route('admin.job-openings.store') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-6">
    @csrf
    @include('admin.job-openings._form')
    <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-brand-red text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-red-dark">Save</button>
        <a href="{{ route('admin.job-openings.index') }}" class="px-5 py-2.5 rounded-lg text-sm font-medium border border-gray-200 text-gray-700 hover:bg-gray-50">Cancel</a>
    </div>
</form>
@endsection
