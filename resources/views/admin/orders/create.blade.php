@extends('layouts.admin')

@section('title', 'Create Order')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.orders.index') }}" class="text-gray-500 hover:text-gray-800">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h1 class="text-2xl font-bold text-gray-900">Create Order</h1>
</div>

<form method="POST" action="{{ route('admin.orders.store') }}" class="space-y-6">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Order Items --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-800 mb-4">Order Items</h2>
                <div id="order-items" class="space-y-3">
                    <div class="grid grid-cols-12 gap-2 items-center">
                        <div class="col-span-5">
                            <input name="items[0][product_name]" placeholder="Product name" required
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                        </div>
                        <div class="col-span-2">
                            <input name="items[0][quantity]" type="number" min="1" value="1" placeholder="Qty" required
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                        </div>
                        <div class="col-span-3">
                            <input name="items[0][unit_price]" type="number" step="0.01" min="0" placeholder="Price" required
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                        </div>
                        <div class="col-span-2 text-right">
                            <button type="button" onclick="removeItem(this)" class="text-red-400 hover:text-red-600 text-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addItem()" class="mt-3 flex items-center gap-1 text-sm text-brand-red hover:underline">
                    <i class="bi bi-plus-circle"></i> Add Item
                </button>
            </div>

            {{-- Notes --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" placeholder="Order notes..."
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red"></textarea>
            </div>
        </div>

        <div class="space-y-5">
            {{-- Customer --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-800 mb-4">Customer</h2>
                <select name="customer_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                    <option value="">Guest</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
                <h2 class="font-semibold text-gray-800 mb-2">Status</h2>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Order Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Payment Status</label>
                    <select name="payment_status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Shipping Cost (Rs.)</label>
                    <input name="shipping_cost" type="number" step="0.01" min="0" value="0"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Discount (Rs.)</label>
                    <input name="discount_amount" type="number" step="0.01" min="0" value="0"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                </div>
            </div>

            <button type="submit" class="w-full bg-brand-red text-white py-2.5 rounded-lg font-medium hover:bg-red-dark transition">
                <i class="bi bi-check-lg me-1"></i> Create Order
            </button>
        </div>
    </div>
</form>

<script>
    let idx = 1;
    function addItem() {
        const container = document.getElementById('order-items');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-12 gap-2 items-center';
        div.innerHTML = `
            <div class="col-span-5"><input name="items[${idx}][product_name]" placeholder="Product name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red"></div>
            <div class="col-span-2"><input name="items[${idx}][quantity]" type="number" min="1" value="1" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red"></div>
            <div class="col-span-3"><input name="items[${idx}][unit_price]" type="number" step="0.01" min="0" placeholder="Price" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red"></div>
            <div class="col-span-2 text-right"><button type="button" onclick="removeItem(this)" class="text-red-400 hover:text-red-600 text-sm"><i class="bi bi-trash"></i></button></div>
        `;
        container.appendChild(div);
        idx++;
    }
    function removeItem(btn) {
        const row = btn.closest('.grid');
        if (document.getElementById('order-items').children.length > 1) row.remove();
    }
</script>
@endsection
