@extends('layouts.admin')

@section('title', 'Edit staff')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.staff.index') }}" class="text-sm text-gray-500 hover:text-brand-red">
        <i class="bi bi-arrow-left"></i> Staff
    </a>
    <h1 class="mt-2 text-2xl font-bold text-gray-900">Edit {{ $staffUser->name }}</h1>
</div>

<form method="POST" action="{{ route('admin.staff.update', $staffUser) }}" class="max-w-xl bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4">
    @csrf
    @method('PUT')
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input name="name" value="{{ old('name', $staffUser->name) }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $staffUser->email) }}" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
        <select name="role" required class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" @disabled($staffUser->is(auth()->user()))>
            @foreach($roles as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $staffUser->role) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @if($staffUser->is(auth()->user()))
            <input type="hidden" name="role" value="{{ $staffUser->role }}">
            <p class="mt-1 text-xs text-gray-500">Your own role stays as it is.</p>
        @endif
        @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">New password</label>
        <input type="password" name="password" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Leave blank to keep the current password">
        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm new password</label>
        <input type="password" name="password_confirmation" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
    </div>
    <button type="submit" class="rounded-lg bg-brand-red text-white px-4 py-2 text-sm font-medium hover:bg-red-dark">Save</button>
</form>
@endsection