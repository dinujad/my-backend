@extends('layouts.admin')

@section('title', 'Edit Post')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.blog.index') }}" class="text-gray-500 hover:text-gray-800"><i class="bi bi-arrow-left"></i></a>
    <h1 class="text-2xl font-bold text-gray-900">Edit Post</h1>
</div>

<form method="POST" action="{{ route('admin.blog.update', $blog) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('admin.blog._form')
</form>
@endsection
