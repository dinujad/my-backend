@extends('layouts.admin')

@section('title', 'New Blog Post')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.blog.index') }}" class="text-gray-500 hover:text-gray-800"><i class="bi bi-arrow-left"></i></a>
    <h1 class="text-2xl font-bold text-gray-900">New Blog Post</h1>
</div>

<form method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data">
    @csrf
    @include('admin.blog._form')
</form>
@endsection
