@extends('layouts.admin')

@section('title', 'Products')

@section('content')
@php use App\Support\ProductMediaPath; @endphp
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-box-seam text-brand-red"></i> Products
    </h1>
    <a href="{{ route('admin.products.create') }}" class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-dark transition text-sm font-medium">
        <i class="bi bi-plus-lg"></i> Add Product
    </a>
</div>

<p class="mb-4 text-sm text-gray-600 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3">
    <strong>Home page:</strong> tick <span class="font-semibold">Special</span> (max 2), <span class="font-semibold">Featured</span>, <span class="font-semibold">Sale</span>, or <span class="font-semibold">Top</span>.
    Set <span class="font-semibold">Offer Rs</span> for the special-offer sidebar price. Changes save automatically.
</p>

<form method="GET" action="{{ route('admin.products.index') }}" class="mb-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
        <div class="md:col-span-2">
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Search</label>
            <input
                type="text"
                name="q"
                value="{{ $filters['q'] ?? '' }}"
                placeholder="Name, slug, SKU, category..."
                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red"
            >
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Category</label>
            <select name="category_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) ($filters['category_id'] ?? 0) === (int) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
            <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red">
                <option value="">All</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Home section</label>
            <select name="home_flag" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red">
                <option value="">All</option>
                <option value="special" @selected(($filters['home_flag'] ?? '') === 'special')>Special</option>
                <option value="featured" @selected(($filters['home_flag'] ?? '') === 'featured')>Featured</option>
                <option value="sale" @selected(($filters['home_flag'] ?? '') === 'sale')>On Sale</option>
                <option value="top" @selected(($filters['home_flag'] ?? '') === 'top')>Top Rated</option>
            </select>
        </div>
    </div>
    <div class="mt-3 flex items-center justify-end gap-2">
        <a href="{{ route('admin.products.index') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">
            Reset
        </a>
        <button type="submit" class="rounded-lg bg-brand-red px-4 py-2 text-sm font-medium text-white hover:bg-red-dark">
            Search / Filter
        </button>
    </div>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
    <table class="w-full text-sm min-w-[960px]">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Product</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Category</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Price</th>
                <th class="px-3 py-3 text-center font-semibold text-gray-600" title="Special Offer (sidebar, max 2)">Special</th>
                <th class="px-3 py-3 text-center font-semibold text-gray-600" title="Featured tab">Featured</th>
                <th class="px-3 py-3 text-center font-semibold text-gray-600" title="On Sale tab">Sale</th>
                <th class="px-3 py-3 text-center font-semibold text-gray-600" title="Top Rated tab">Top</th>
                <th class="px-3 py-3 text-center font-semibold text-gray-600">Offer Rs</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50" id="products-homepage-table">
            @forelse($products as $product)
                @php
                    $imgPath = $product->image ? ProductMediaPath::publicUrl($product->image) : '';
                @endphp
                <tr class="hover:bg-gray-50" data-product-row="{{ $product->id }}"
                    data-update-url="{{ route('admin.products.homepage-flags', $product) }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($imgPath)
                                <img src="{{ $imgPath }}" alt="{{ $product->name }}" class="w-9 h-9 rounded-lg object-cover bg-gray-100">
                            @else
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <i class="bi bi-image text-gray-400"></i>
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $product->slug }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $product->category?->name ?? '–' }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-900 whitespace-nowrap">{{ $product->adminListPrice() }}</td>
                    <td class="px-3 py-3 text-center">
                        <input type="checkbox" class="hp-flag rounded border-gray-300 text-brand-red focus:ring-brand-red"
                               data-field="is_special_offer" data-product-id="{{ $product->id }}"
                               @checked($product->is_special_offer) title="Special Offer">
                    </td>
                    <td class="px-3 py-3 text-center">
                        <input type="checkbox" class="hp-flag rounded border-gray-300 text-brand-red focus:ring-brand-red"
                               data-field="is_featured" data-product-id="{{ $product->id }}"
                               @checked($product->is_featured) title="Featured">
                    </td>
                    <td class="px-3 py-3 text-center">
                        <input type="checkbox" class="hp-flag rounded border-gray-300 text-brand-red focus:ring-brand-red"
                               data-field="is_on_sale" data-product-id="{{ $product->id }}"
                               @checked($product->is_on_sale) title="On Sale">
                    </td>
                    <td class="px-3 py-3 text-center">
                        <input type="checkbox" class="hp-flag rounded border-gray-300 text-brand-red focus:ring-brand-red"
                               data-field="is_top_rated" data-product-id="{{ $product->id }}"
                               @checked($product->is_top_rated) title="Top Rated">
                    </td>
                    <td class="px-3 py-3 text-center">
                        <input type="number" step="0.01" min="0" placeholder="—"
                               class="hp-offer-price w-24 border border-gray-200 rounded px-2 py-1 text-xs text-center focus:border-brand-red outline-none"
                               data-product-id="{{ $product->id }}"
                               value="{{ $product->offer_price ? (float) $product->offer_price : '' }}">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center gap-1 text-brand-red hover:underline text-xs">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="px-4 py-10 text-center text-gray-400">No products found for current filter. <a href="{{ route('admin.products.index') }}" class="text-brand-red hover:underline">Clear filters</a>.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($products->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $products->links() }}</div>
    @endif
</div>

<div id="hp-toast" class="fixed bottom-4 right-4 z-50 hidden max-w-sm rounded-lg px-4 py-3 text-sm text-white shadow-lg"></div>

<script>
(function () {
    const csrf = @json(csrf_token());
    const toast = document.getElementById('hp-toast');
    let toastTimer;

    function showToast(msg, ok) {
        toast.textContent = msg;
        toast.className = 'fixed bottom-4 right-4 z-50 max-w-sm rounded-lg px-4 py-3 text-sm text-white shadow-lg ' + (ok ? 'bg-emerald-600' : 'bg-red-600');
        toast.classList.remove('hidden');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.add('hidden'), 3500);
    }

    function rowPayload(row) {
        if (!row) return {};
        return {
            is_special_offer: row.querySelector('[data-field="is_special_offer"]')?.checked ? 1 : 0,
            is_featured: row.querySelector('[data-field="is_featured"]')?.checked ? 1 : 0,
            is_on_sale: row.querySelector('[data-field="is_on_sale"]')?.checked ? 1 : 0,
            is_top_rated: row.querySelector('[data-field="is_top_rated"]')?.checked ? 1 : 0,
            offer_price: row.querySelector('.hp-offer-price')?.value ?? '',
        };
    }

    async function saveFlags(productId) {
        const row = document.querySelector(`tr[data-product-row="${productId}"]`);
        if (!row?.dataset.updateUrl) return;
        try {
            const res = await fetch(row.dataset.updateUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify(rowPayload(row)),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(data.message || (data.errors && Object.values(data.errors).flat()[0]) || 'Save failed');
            }
            showToast('Home page settings saved', true);
        } catch (e) {
            showToast(e.message || 'Could not save', false);
        }
    }

    document.querySelectorAll('.hp-flag').forEach((el) => {
        el.addEventListener('change', () => saveFlags(el.dataset.productId));
    });

    document.querySelectorAll('.hp-offer-price').forEach((el) => {
        el.addEventListener('change', () => saveFlags(el.dataset.productId));
        el.addEventListener('blur', () => saveFlags(el.dataset.productId));
    });
})();
</script>
@endsection
