@extends('layouts.admin')

@section('title', $zone ? 'Edit Zone' : 'Create Zone')

@section('content')
<div class="p-6 max-w-3xl mx-auto">

  <div class="mb-6">
    <a href="{{ route('admin.shipping.index') }}" class="text-sm text-gray-500 hover:text-brand-red flex items-center gap-1">
      <i class="bi bi-arrow-left"></i> Back to Shipping
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2 flex items-center gap-2">
      <i class="bi bi-map text-brand-red"></i>
      {{ $zone ? 'Edit Zone: ' . $zone->name : 'Create Shipping Zone' }}
    </h1>
  </div>

  <form method="POST"
        action="{{ $zone ? route('admin.shipping.zones.update', $zone) : route('admin.shipping.zones.store') }}"
        class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    @csrf
    @if($zone) @method('PUT') @endif

    @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
      <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    {{-- Name --}}
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">Zone Name <span class="text-brand-red">*</span></label>
      <input type="text" name="name" value="{{ old('name', $zone?->name) }}"
             class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
             placeholder="e.g. Western Province" required>
    </div>

    {{-- Description --}}
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
      <input type="text" name="description" value="{{ old('description', $zone?->description) }}"
             class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
             placeholder="Optional description">
    </div>

    <div class="grid grid-cols-2 gap-4">
      {{-- Sort Order --}}
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $zone?->sort_order ?? 0) }}" min="0"
               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20">
      </div>
      {{-- Active --}}
      <div class="flex items-end pb-1">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active', $zone?->is_active ?? true) ? 'checked' : '' }}
                 class="h-5 w-5 rounded border-gray-300 text-brand-red focus:ring-brand-red">
          <span class="text-sm font-semibold text-gray-700">Active</span>
        </label>
      </div>
    </div>

    {{-- District Assignment (edit only) --}}
    @if($zone && isset($allDistricts))
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-2">Assign Districts to this Zone</label>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 rounded-xl border border-gray-200 bg-gray-50 p-4 max-h-72 overflow-y-auto">
        @foreach($allDistricts as $district)
        <label class="flex items-center gap-2 cursor-pointer py-1">
          <input type="checkbox" name="district_ids[]" value="{{ $district->id }}"
                 {{ in_array($district->id, $zone->districts->pluck('id')->toArray()) ? 'checked' : '' }}
                 class="h-4 w-4 rounded border-gray-300 text-brand-red focus:ring-brand-red">
          <span class="text-sm text-gray-700">{{ $district->name }}</span>
        </label>
        @endforeach
      </div>
    </div>
    @endif

    {{-- Actions --}}
    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
      <a href="{{ route('admin.shipping.index') }}"
         class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
        Cancel
      </a>
      <button type="submit"
              class="rounded-xl bg-brand-red px-6 py-2.5 text-sm font-bold text-white shadow hover:bg-red-700 transition">
        {{ $zone ? 'Update Zone' : 'Create Zone' }}
      </button>
    </div>
  </form>
</div>
@endsection
