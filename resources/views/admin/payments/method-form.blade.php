@extends('layouts.admin')

@section('title', $method ? 'Edit Payment Method' : 'Add Payment Method')

@section('content')
<div class="p-6 max-w-2xl mx-auto">

  <div class="mb-6">
    <a href="{{ route('admin.payments.index') }}" class="text-sm text-gray-500 hover:text-brand-red flex items-center gap-1">
      <i class="bi bi-arrow-left"></i> Back to Payment Methods
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2 flex items-center gap-2">
      <i class="bi bi-credit-card text-brand-red"></i>
      {{ $method ? 'Edit: ' . $method->name : 'Add Payment Method' }}
    </h1>
  </div>

  <form method="POST"
        action="{{ $method ? route('admin.payments.update', $method) : route('admin.payments.store') }}"
        class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    @csrf
    @if($method) @method('PUT') @endif

    @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
      <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
    @endif

    {{-- Code (create only) --}}
    @if(!$method)
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">
        Code <span class="text-brand-red">*</span>
        <span class="text-xs font-normal text-gray-400 ml-1">Unique identifier (lowercase, no spaces)</span>
      </label>
      <input type="text" name="code" value="{{ old('code') }}" required
             class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 font-mono outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
             placeholder="e.g. bank_transfer">
    </div>
    @else
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">Code</label>
      <p class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-gray-500 font-mono text-sm">{{ $method->code }}</p>
    </div>
    @endif

    {{-- Name --}}
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">Name <span class="text-brand-red">*</span></label>
      <input type="text" name="name" value="{{ old('name', $method?->name) }}" required
             class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
             placeholder="e.g. Bank Transfer">
    </div>

    {{-- Description --}}
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
      <textarea name="description" rows="2"
                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
                placeholder="Short description shown to customers">{{ old('description', $method?->description) }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
      {{-- Type --}}
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Type <span class="text-brand-red">*</span></label>
        <select name="type" required
                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20 bg-white">
          <option value="offline" {{ old('type', $method?->type) === 'offline' ? 'selected' : '' }}>Offline</option>
          <option value="online"  {{ old('type', $method?->type) === 'online'  ? 'selected' : '' }}>Online</option>
        </select>
      </div>

      {{-- Sort Order --}}
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Sort Order</label>
        <input type="number" name="sort_order" min="0"
               value="{{ old('sort_order', $method?->sort_order ?? 0) }}"
               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20">
      </div>
    </div>

    {{-- Active --}}
    <div>
      <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $method?->is_active ?? true) ? 'checked' : '' }}
               class="h-5 w-5 rounded border-gray-300 text-brand-red focus:ring-brand-red">
        <span class="text-sm font-semibold text-gray-700">Active (visible at checkout)</span>
      </label>
    </div>

    {{-- Actions --}}
    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
      <a href="{{ route('admin.payments.index') }}"
         class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
        Cancel
      </a>
      <button type="submit"
              class="rounded-xl bg-brand-red px-6 py-2.5 text-sm font-bold text-white shadow hover:bg-red-700 transition">
        {{ $method ? 'Update Method' : 'Add Method' }}
      </button>
    </div>
  </form>
</div>
@endsection
