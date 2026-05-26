@extends('layouts.admin')

@section('title', 'Edit Promo Banner')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Edit promo banner</h1>

<form method="POST" action="{{ route('admin.promo-banners.update', $banner) }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
    @csrf
    @method('PUT')
    @include('admin.promo-banners._form', ['banner' => $banner])
    @if($errors->any())
        <div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="mt-6 flex gap-3">
        <button type="submit" class="bg-brand-red text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-dark">Update banner</button>
        <a href="{{ route('admin.promo-banners.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
    </div>
</form>
@endsection
