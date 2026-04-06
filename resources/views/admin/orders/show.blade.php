@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.orders.index') }}" class="text-gray-500 hover:text-gray-800">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Order {{ $order->order_number }}</h1>
    @php
        $statusColors = [
            'pending' => 'bg-yellow-100 text-yellow-700',
            'processing' => 'bg-blue-100 text-blue-700',
            'shipped' => 'bg-purple-100 text-purple-700',
            'delivered' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
        ];
    @endphp
    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
        {{ ucfirst($order->status) }}
    </span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Order Items --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800 flex items-center gap-2">
                <i class="bi bi-list-ul text-brand-red"></i> Order Items
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600 font-medium">Product</th>
                        <th class="px-4 py-2 text-center text-gray-600 font-medium">Qty</th>
                        <th class="px-4 py-2 text-right text-gray-600 font-medium">Unit Price</th>
                        <th class="px-4 py-2 text-right text-gray-600 font-medium">Customization</th>
                        <th class="px-4 py-2 text-right text-gray-600 font-medium">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($order->items as $item)
                        <tr class="align-top">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $item->product_name }}
                                @if(!empty($item->customizations) && is_array($item->customizations))
                                    <div class="mt-1 space-y-0.5">
                                        @foreach($item->customizations as $label => $value)
                                            @if(!str_starts_with($value, '/storage/'))
                                                <div class="text-xs text-gray-500">
                                                    <span class="font-semibold text-gray-600">{{ $label }}:</span> {{ $value }}
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-500">
                                                    <span class="font-semibold text-gray-600">{{ $label }}:</span>
                                                    <a href="{{ asset($value) }}" target="_blank" class="text-brand-red hover:underline ml-1">
                                                        <i class="bi bi-paperclip"></i> View file
                                                    </a>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">
                                @if($item->customization_fee > 0)
                                    <span class="text-brand-red font-medium">+ Rs. {{ number_format($item->customization_fee, 2) }}</span>
                                    <div class="text-xs text-gray-400">per unit</div>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-gray-200 bg-gray-50 text-sm">
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-gray-600">Subtotal</td>
                        <td class="px-4 py-2 text-right font-medium">Rs. {{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-green-600">Discount</td>
                            <td class="px-4 py-2 text-right text-green-600">- Rs. {{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-gray-600">Shipping</td>
                        <td class="px-4 py-2 text-right font-medium">Rs. {{ number_format($order->shipping_cost, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right font-bold text-gray-900">Total</td>
                        <td class="px-4 py-2 text-right font-bold text-brand-red">Rs. {{ number_format($order->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($order->notes)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                    <i class="bi bi-chat-left-text text-brand-red"></i> Notes
                </h3>
                <p class="text-sm text-gray-600">{{ $order->notes }}</p>
            </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Update Status --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="bi bi-arrow-repeat text-brand-red"></i> Update Status
            </h3>
            <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}" class="space-y-3" x-data="{ status: '{{ $order->status }}' }">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Order Status</label>
                    <select name="status" x-model="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                        @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
                            <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div x-show="status === 'shipped'" class="space-y-3 p-3 bg-blue-50/50 border border-blue-100 rounded-lg" style="display: none;" x-transition>
                    <div class="text-[10px] text-blue-600 font-bold uppercase tracking-wider mb-2"><i class="bi bi-whatsapp"></i> Customer will be notified</div>
                    <div>
                        <label class="block text-xs font-medium text-blue-800 mb-1">Delivery Service / Courier</label>
                        <input type="text" name="shipping_method" value="{{ old('shipping_method', $order->shipments->first()->shipping_method ?? 'Koombiyo Delivery') }}" class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-blue-800 mb-1">Tracking Number</label>
                        <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->shipments->first()->tracking_number ?? '') }}" class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500 bg-white" placeholder="Optional">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Payment Status</label>
                    <select name="payment_status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                        @foreach(['unpaid','paid','refunded'] as $s)
                            <option value="{{ $s }}" {{ $order->payment_status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full bg-brand-red text-white py-2 rounded-lg text-sm font-medium hover:bg-red-dark transition">
                    Update Status
                </button>
            </form>
        </div>

        {{-- Customer / Shipping Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <i class="bi bi-person text-brand-red"></i> Customer &amp; Delivery
            </h3>
            @php $addr = $order->shipping_address ?? []; @endphp
            @if(!empty($addr))
                <p class="text-sm font-medium text-gray-900">{{ $addr['name'] ?? '—' }}</p>
                <p class="text-sm text-gray-500">{{ $addr['email'] ?? '' }}</p>
                <p class="text-sm text-gray-500">{{ $addr['phone'] ?? '' }}</p>
                @if(!empty($addr['address']))
                    <p class="mt-1 text-sm text-gray-600 whitespace-pre-line">{{ $addr['address'] }}</p>
                @endif
            @elseif($order->customer)
                <p class="text-sm font-medium text-gray-900">{{ $order->customer->name }}</p>
                <p class="text-sm text-gray-500">{{ $order->customer->email }}</p>
                @if($order->customer->phone)
                    <p class="text-sm text-gray-500">{{ $order->customer->phone }}</p>
                @endif
                <a href="{{ route('admin.customers.show', $order->customer) }}" class="block mt-2 text-xs text-brand-red hover:underline">View profile</a>
            @else
                <p class="text-sm text-gray-400">Guest order — no details saved</p>
            @endif
        </div>

        {{-- Delete --}}
        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}"
              onsubmit="return confirm('Delete this order?')">
            @csrf @method('DELETE')
            <button type="submit" class="w-full flex items-center justify-center gap-2 border border-red-200 text-red-600 py-2 rounded-lg text-sm hover:bg-red-50 transition">
                <i class="bi bi-trash"></i> Delete Order
            </button>
        </form>
    </div>
</div>
@endsection
