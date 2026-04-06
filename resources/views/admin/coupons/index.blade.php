@extends('layouts.admin')

@section('title', 'Coupons')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-tag text-brand-red"></i> Coupons
    </h1>
    <a href="{{ route('admin.coupons.create') }}" class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-dark transition text-sm font-medium">
        <i class="bi bi-plus-lg"></i> New Coupon
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Code</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Discount</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Min Order</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Used</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Expires</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($coupons as $coupon)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-bold text-gray-900 tracking-wide">{{ $coupon->code }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ $coupon->type === 'percent' ? $coupon->value . '%' : 'Rs. ' . number_format($coupon->value, 2) }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $coupon->min_order ? 'Rs. ' . number_format($coupon->min_order, 0) : '–' }}
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600">
                        {{ $coupon->used_count }}{{ $coupon->usage_limit ? '/' . $coupon->usage_limit : '' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $coupon->expires_at?->format('d M Y') ?? '–' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($coupon->isValid())
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right flex items-center justify-end gap-3">
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-blue-500 hover:underline text-xs">Edit</a>
                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Delete coupon?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No coupons yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($coupons->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $coupons->links() }}</div>
    @endif
</div>
@endsection
