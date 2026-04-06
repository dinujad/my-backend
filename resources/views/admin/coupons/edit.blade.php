@extends('layouts.admin')

@section('title', 'Edit Coupon')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.coupons.index') }}" class="text-gray-500 hover:text-gray-800"><i class="bi bi-arrow-left"></i></a>
    <h1 class="text-2xl font-bold text-gray-900">Edit Coupon</h1>
</div>

<div class="max-w-lg">
    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Coupon Code</label>
            <input name="code" value="{{ old('code', $coupon->code) }}" required style="text-transform:uppercase"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:border-brand-red tracking-widest">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                    <option value="percent" {{ $coupon->type === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ $coupon->type === 'fixed' ? 'selected' : '' }}>Fixed Amount (Rs.)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount Value</label>
                <input name="value" type="number" step="0.01" min="0" value="{{ old('value', $coupon->value) }}" required
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min Order (Rs.)</label>
                <input name="min_order" type="number" step="0.01" min="0" value="{{ old('min_order', $coupon->min_order) }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usage Limit</label>
                <input name="usage_limit" type="number" min="1" value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="Unlimited"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input name="starts_at" type="date" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d')) }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                <input name="expires_at" type="date" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d')) }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            </div>
        </div>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ $coupon->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-brand-red">
            <span class="text-sm text-gray-700">Active</span>
        </label>
        <div class="flex gap-3">
            <button type="submit" class="flex-1 bg-brand-red text-white py-2.5 rounded-lg font-medium hover:bg-red-dark transition">Save Changes</button>
            <a href="{{ route('admin.coupons.index') }}" class="flex-1 text-center border border-gray-200 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
