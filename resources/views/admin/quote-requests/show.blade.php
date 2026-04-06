@extends('layouts.admin')

@section('title', 'Quote Request ' . $quoteRequest->request_number)

@section('content')
<div class="mb-5">
    <a href="{{ route('admin.quote-requests.index') }}" class="text-sm text-gray-500 hover:text-brand-red">
        <i class="bi bi-arrow-left"></i> Back to Quote Requests
    </a>
</div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-green-100 text-green-800 text-sm">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-red-100 text-red-800 text-sm">
        <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
    </div>
@endif

{{-- Header --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $quoteRequest->request_number }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">Submitted {{ $quoteRequest->created_at->format('d M Y, H:i') }}</p>
    </div>
    <form method="POST" action="{{ route('admin.quote-requests.status', $quoteRequest) }}" class="flex items-center gap-2">
        @csrf
        <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
            @foreach(\App\Models\QuoteRequest::$statuses as $key => $label)
                <option value="{{ $key }}" @selected($quoteRequest->status === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-brand-red text-white px-4 py-2 text-sm font-medium hover:bg-red-dark">Update Status</button>
    </form>
</div>

@php
    $latestQuotation = $quoteRequest->quotations->first();
    $initialItems = $latestQuotation
        ? $latestQuotation->items->map(function ($i) {
            return [
                'description' => $i->description,
                'quantity' => $i->quantity,
                'unit_price' => (float) $i->unit_price,
                'discount_percent' => (float) $i->discount_percent,
                'item_notes' => $i->item_notes ?? '',
            ];
        })->values()
        : $quoteRequest->items->map(function ($i) {
            return [
                'description' => $i->product_name,
                'quantity' => $i->quantity,
                'unit_price' => 0,
                'discount_percent' => 0,
                'item_notes' => $i->item_notes ?? '',
            ];
        })->values();

    $waLogsInitial = $latestQuotation
        ? $latestQuotation->whatsappLogs->map(function ($l) {
            return [
                'id' => $l->id,
                'success' => $l->success,
                'sent_at' => optional($l->sent_at)->format('d M H:i'),
            ];
        })->values()
        : collect([]);
@endphp

<div x-data="quotationBuilder()" x-init="init()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ====== LEFT COLUMN ====== --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Customer Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="bi bi-person-circle text-brand-red"></i> Customer Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-500">Name:</span> <span class="font-medium">{{ $quoteRequest->customer_name }}</span></div>
                <div><span class="text-gray-500">Company:</span> <span class="font-medium">{{ $quoteRequest->company_name ?: '-' }}</span></div>
                <div><span class="text-gray-500">Email:</span> <span class="font-medium">{{ $quoteRequest->email }}</span></div>
                <div><span class="text-gray-500">Phone / WhatsApp:</span> <span class="font-medium">{{ $quoteRequest->phone }}</span></div>
                <div><span class="text-gray-500">Preferred Contact:</span> <span class="font-medium capitalize">{{ $quoteRequest->preferred_contact }}</span></div>
                <div><span class="text-gray-500">Preferred Response:</span> <span class="font-medium capitalize">{{ $quoteRequest->preferred_response }}</span></div>
                <div><span class="text-gray-500">Urgency:</span> <span class="font-medium capitalize">{{ $quoteRequest->urgency ?: '-' }}</span></div>
                <div><span class="text-gray-500">Deadline:</span> <span class="font-medium">{{ optional($quoteRequest->deadline)->format('d M Y') ?: '-' }}</span></div>
            </div>
            @if($quoteRequest->address)
                <div class="mt-3 text-sm"><span class="text-gray-500">Address:</span> <span class="font-medium">{{ $quoteRequest->address }}</span></div>
            @endif
            @if($quoteRequest->customer_notes)
                <div class="mt-4 p-3 rounded-lg bg-red-50 border border-red-100 text-sm text-gray-700">
                    <span class="font-semibold text-red-700">Customer Notes:</span><br>
                    {{ $quoteRequest->customer_notes }}
                </div>
            @endif
        </div>

        {{-- Requested Items --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="bi bi-bag text-brand-red"></i> Requested Items ({{ $quoteRequest->items->count() }})
            </h2>
            <div class="space-y-3">
                @foreach($quoteRequest->items as $item)
                    <div class="border border-gray-100 rounded-lg p-3 flex items-start gap-3">
                        @if($item->product_image)
                            <img src="{{ str_starts_with($item->product_image, 'http') ? $item->product_image : asset('storage/' . ltrim($item->product_image, '/')) }}" alt="" class="w-14 h-14 rounded object-cover border border-gray-100">
                        @else
                            <div class="w-14 h-14 rounded bg-gray-100 flex items-center justify-center text-gray-400 shrink-0">
                                <i class="bi bi-image text-xl"></i>
                            </div>
                        @endif
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">{{ $item->product_name }}</div>
                            @if($item->product_sku)
                                <div class="text-xs text-gray-500">SKU: {{ $item->product_sku }}</div>
                            @endif
                            @if($item->variation_attributes)
                                <div class="text-xs text-gray-500 mt-1">
                                    @foreach($item->variation_attributes as $k => $v)
                                        <span>{{ $k }}: {{ $v }}</span>
                                        @if(!$loop->last)
                                            <span> · </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            @if($item->item_notes)
                                <div class="text-xs text-gray-500 mt-1 italic">{{ $item->item_notes }}</div>
                            @endif
                        </div>
                        <div class="shrink-0 text-sm font-bold text-gray-700 bg-gray-100 rounded-full px-3 py-1">× {{ $item->quantity }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ====== QUOTATION BUILDER ====== --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="bi bi-receipt text-brand-red"></i>
                    <span x-text="quotationId ? 'Edit Quotation' : 'Create Quotation'">Quotation Builder</span>
                    <span x-show="quotationNumber" x-text="'#' + quotationNumber" class="ml-1 text-sm text-indigo-600 font-bold"></span>
                </h2>
                <span x-show="qtStatus" x-text="qtStatus" class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 capitalize"></span>
            </div>

            {{-- Line items table --}}
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm min-w-[600px]">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-bold uppercase tracking-wider text-gray-400">
                            <th class="pb-2 text-left pl-1 w-[40%]">Description</th>
                            <th class="pb-2 text-center w-16">Qty</th>
                            <th class="pb-2 text-right w-28">Unit Price (Rs.)</th>
                            <th class="pb-2 text-center w-20">Disc %</th>
                            <th class="pb-2 text-right w-28">Subtotal</th>
                            <th class="pb-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr class="border-b border-gray-50">
                                <td class="py-2 pr-2">
                                    <input x-model="item.description" type="text" placeholder="Item description"
                                        class="w-full rounded border border-gray-200 px-2 py-1.5 text-sm outline-none focus:border-brand-red">
                                    <input x-model="item.item_notes" type="text" placeholder="Notes (optional)"
                                        class="mt-1 w-full rounded border border-gray-100 bg-gray-50 px-2 py-1 text-xs text-gray-500 outline-none">
                                </td>
                                <td class="py-2 px-1 text-center">
                                    <input x-model.number="item.quantity" type="number" min="1"
                                        class="w-16 rounded border border-gray-200 py-1.5 text-center text-sm outline-none focus:border-brand-red">
                                </td>
                                <td class="py-2 px-1">
                                    <input x-model.number="item.unit_price" type="number" min="0" step="0.01"
                                        class="w-full rounded border border-gray-200 py-1.5 text-right text-sm outline-none focus:border-brand-red">
                                </td>
                                <td class="py-2 px-1 text-center">
                                    <input x-model.number="item.discount_percent" type="number" min="0" max="100" step="0.5"
                                        class="w-16 rounded border border-gray-200 py-1.5 text-center text-sm outline-none focus:border-brand-red">
                                </td>
                                <td class="py-2 px-1 text-right font-semibold text-gray-800"
                                    x-text="'Rs. ' + lineSubtotal(item).toLocaleString('en-LK', {minimumFractionDigits:2})"></td>
                                <td class="py-2 pl-1">
                                    <button type="button" @click="items.splice(idx,1)"
                                        class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-500 transition">
                                        <i class="bi bi-x-lg text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <button type="button" @click="addItem()"
                    class="mt-3 w-full rounded-lg border-2 border-dashed border-gray-200 py-2.5 text-sm font-medium text-gray-400 hover:border-brand-red hover:text-brand-red transition">
                    <i class="bi bi-plus-lg mr-1"></i> Add line item
                </button>
            </div>

            {{-- Totals --}}
            <div class="flex justify-end mb-5">
                <div class="w-72 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-semibold" x-text="'Rs. ' + subtotal().toLocaleString('en-LK', {minimumFractionDigits:2})"></span>
                    </div>
                    <div class="flex items-center justify-between gap-2 text-gray-600">
                        <span>Overall Discount (Rs.)</span>
                        <input x-model.number="meta.discount_amount" type="number" min="0" step="0.01"
                            class="w-28 rounded border border-gray-200 py-1 text-right text-sm px-2 outline-none focus:border-brand-red">
                    </div>
                    <div class="flex items-center justify-between gap-2 text-gray-600">
                        <span>Tax (Rs.)</span>
                        <input x-model.number="meta.tax_amount" type="number" min="0" step="0.01"
                            class="w-28 rounded border border-gray-200 py-1 text-right text-sm px-2 outline-none focus:border-brand-red">
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-900">
                        <span>Grand Total</span>
                        <span class="text-brand-red" x-text="'Rs. ' + grandTotal().toLocaleString('en-LK', {minimumFractionDigits:2})"></span>
                    </div>
                </div>
            </div>

            {{-- Meta fields --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">Valid Until</label>
                    <input x-model="meta.valid_until" type="date"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">Payment Terms</label>
                    <input x-model="meta.payment_terms" type="text" placeholder="e.g. 50% advance, balance on delivery"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">Delivery Details</label>
                    <input x-model="meta.delivery_details" type="text" placeholder="e.g. 3-5 working days"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">Notes</label>
                    <input x-model="meta.notes" type="text" placeholder="Any additional notes"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">Terms & Conditions</label>
                    <textarea x-model="meta.terms_conditions" rows="2" placeholder="Standard T&C..."
                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red resize-none"></textarea>
                </div>
            </div>

            {{-- Error --}}
            <div x-show="saveError" x-text="saveError" class="mb-3 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600"></div>

            {{-- Save button --}}
            <button type="button" @click="saveQuotation()"
                :disabled="saving"
                class="w-full rounded-xl bg-gray-900 text-white py-3 text-sm font-bold hover:bg-black transition disabled:opacity-60">
                <span x-show="!saving" x-text="quotationId ? 'Update Quotation' : 'Create Quotation'"></span>
                <span x-show="saving"><i class="bi bi-arrow-repeat animate-spin"></i> Saving…</span>
            </button>
        </div>

        {{-- ====== QUOTATION PREVIEW ====== --}}
        <div x-show="quotationId" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i class="bi bi-file-earmark-richtext text-brand-red"></i> Quotation Preview
                </h2>
                <button type="button" @click="showPreview = !showPreview"
                    class="text-sm text-brand-red hover:underline" x-text="showPreview ? 'Hide preview' : 'Show preview'"></button>
            </div>
            <div x-show="showPreview" class="p-0">
                <div id="quotation-print-area" class="p-8">
                    {{-- Logo + Header --}}
                    <div class="flex items-start justify-between mb-8">
                        <div>
                            <img src="{{ asset('logo.png') }}" alt="Print Works LK" class="h-12 object-contain mb-2">
                            <p class="text-xs text-gray-500">printworks.lk</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-extrabold text-gray-900">QUOTATION</p>
                            <p class="text-sm font-bold text-indigo-600 mt-1" x-text="quotationNumber ? '#' + quotationNumber : ''"></p>
                            <p class="text-xs text-gray-500 mt-1" x-text="'Date: ' + new Date().toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'})"></p>
                            <p class="text-xs text-gray-500" x-text="meta.valid_until ? 'Valid until: ' + meta.valid_until : ''"></p>
                        </div>
                    </div>

                    {{-- Customer --}}
                    <div class="mb-6 p-4 rounded-xl bg-gray-50">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Prepared For</p>
                        <p class="font-bold text-gray-900">{{ $quoteRequest->customer_name }}</p>
                        @if($quoteRequest->company_name)<p class="text-sm text-gray-600">{{ $quoteRequest->company_name }}</p>@endif
                        <p class="text-sm text-gray-600">{{ $quoteRequest->email }}</p>
                        <p class="text-sm text-gray-600">{{ $quoteRequest->phone }}</p>
                        @if($quoteRequest->address)<p class="text-sm text-gray-500">{{ $quoteRequest->address }}</p>@endif
                    </div>

                    {{-- Items table --}}
                    <table class="w-full text-sm mb-6 border-collapse">
                        <thead>
                            <tr class="bg-gray-900 text-white text-xs uppercase tracking-wide">
                                <th class="px-4 py-3 text-left rounded-tl-lg">Description</th>
                                <th class="px-3 py-3 text-center w-16">Qty</th>
                                <th class="px-3 py-3 text-right w-28">Unit Price</th>
                                <th class="px-3 py-3 text-center w-16">Disc</th>
                                <th class="px-4 py-3 text-right w-28 rounded-tr-lg">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, idx) in items" :key="idx">
                                <tr class="border-b border-gray-100">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900" x-text="item.description"></p>
                                        <p class="text-xs text-gray-400 mt-0.5" x-show="item.item_notes" x-text="item.item_notes"></p>
                                    </td>
                                    <td class="px-3 py-3 text-center font-medium" x-text="item.quantity"></td>
                                    <td class="px-3 py-3 text-right" x-text="'Rs. ' + Number(item.unit_price).toLocaleString('en-LK',{minimumFractionDigits:2})"></td>
                                    <td class="px-3 py-3 text-center text-red-500 font-medium" x-text="item.discount_percent > 0 ? item.discount_percent + '%' : '-'"></td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900" x-text="'Rs. ' + lineSubtotal(item).toLocaleString('en-LK',{minimumFractionDigits:2})"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    {{-- Totals --}}
                    <div class="flex justify-end mb-6">
                        <div class="w-64 text-sm space-y-1.5">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span x-text="'Rs. ' + subtotal().toLocaleString('en-LK',{minimumFractionDigits:2})"></span>
                            </div>
                            <div x-show="meta.discount_amount > 0" class="flex justify-between text-red-500">
                                <span>Discount</span>
                                <span x-text="'- Rs. ' + Number(meta.discount_amount).toLocaleString('en-LK',{minimumFractionDigits:2})"></span>
                            </div>
                            <div x-show="meta.tax_amount > 0" class="flex justify-between text-gray-600">
                                <span>Tax</span>
                                <span x-text="'Rs. ' + Number(meta.tax_amount).toLocaleString('en-LK',{minimumFractionDigits:2})"></span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-extrabold text-gray-900">
                                <span>TOTAL</span>
                                <span class="text-brand-red" x-text="'Rs. ' + grandTotal().toLocaleString('en-LK',{minimumFractionDigits:2})"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Terms --}}
                    <div class="space-y-3 text-xs text-gray-500">
                        <div x-show="meta.payment_terms">
                            <p class="font-bold text-gray-700 uppercase tracking-wide mb-1">Payment Terms</p>
                            <p x-text="meta.payment_terms"></p>
                        </div>
                        <div x-show="meta.delivery_details">
                            <p class="font-bold text-gray-700 uppercase tracking-wide mb-1">Delivery</p>
                            <p x-text="meta.delivery_details"></p>
                        </div>
                        <div x-show="meta.terms_conditions">
                            <p class="font-bold text-gray-700 uppercase tracking-wide mb-1">Terms & Conditions</p>
                            <p x-text="meta.terms_conditions"></p>
                        </div>
                        <div x-show="meta.notes">
                            <p class="font-bold text-gray-700 uppercase tracking-wide mb-1">Notes</p>
                            <p x-text="meta.notes"></p>
                        </div>
                    </div>

                    <div class="mt-8 pt-4 border-t border-gray-100 flex justify-between items-center text-xs text-gray-400">
                        <span>Print Works LK &bull; printworks.lk</span>
                        <span>Thank you for your business!</span>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end left col --}}

    {{-- ====== RIGHT COLUMN ====== --}}
    <div class="space-y-5">

        {{-- Send Actions --}}
        <div x-show="quotationId" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                <i class="bi bi-send text-brand-red"></i> Send Quotation
            </h2>

            {{-- WhatsApp Send --}}
            <button type="button" @click="sendWhatsApp()"
                :disabled="sendingWa"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-500 text-white py-3 text-sm font-bold hover:bg-green-600 transition disabled:opacity-60 shadow-sm shadow-green-200">
                <span x-show="!sendingWa">
                    <svg class="w-5 h-5 inline -mt-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    Send via WhatsApp
                </span>
                <span x-show="sendingWa"><i class="bi bi-arrow-repeat animate-spin"></i> Sending…</span>
            </button>

            {{-- Download / Print --}}
            <button type="button" @click="printQuotation()"
                class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white text-gray-700 py-2.5 text-sm font-bold hover:bg-gray-50 transition">
                <i class="bi bi-printer"></i> Print / Download PDF
            </button>

            {{-- Send result --}}
            <div x-show="waResult" x-text="waResult"
                :class="waSuccess ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'"
                class="rounded-lg px-3 py-2 text-xs font-medium"></div>

            {{-- WA send log --}}
            <div x-show="waLogs.length > 0">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Send History</p>
                <template x-for="log in waLogs.slice(0,3)" :key="log.id">
                    <div class="flex items-center gap-2 text-xs mb-1">
                        <span :class="log.success ? 'bg-green-500' : 'bg-red-400'" class="w-2 h-2 rounded-full shrink-0"></span>
                        <span class="text-gray-500" x-text="log.sent_at"></span>
                        <span :class="log.success ? 'text-green-600' : 'text-red-500'" x-text="log.success ? 'Sent ✓' : 'Failed ✗'"></span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Admin Notes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                <i class="bi bi-journal-text text-brand-red"></i> Admin Notes
            </h2>
            <form method="POST" action="{{ route('admin.quote-requests.notes', $quoteRequest) }}">
                @csrf
                <textarea name="admin_notes" rows="5"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red resize-none"
                    placeholder="Internal notes for this quote request...">{{ old('admin_notes', $quoteRequest->admin_notes) }}</textarea>
                <button type="submit" class="mt-3 w-full rounded-lg bg-gray-900 text-white px-4 py-2 text-sm font-medium hover:bg-black">
                    Save Notes
                </button>
            </form>
        </div>

        {{-- Status History --}}
        @if($quoteRequest->statusLogs->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                <i class="bi bi-clock-history text-brand-red"></i> Status History
            </h2>
            <div class="space-y-2">
                @foreach($quoteRequest->statusLogs->sortByDesc('created_at')->take(8) as $log)
                    @php
                        $statusColors = [
                            'new'=>'bg-red-100 text-red-700','reviewing'=>'bg-yellow-100 text-yellow-700',
                            'awaiting_pricing'=>'bg-orange-100 text-orange-700','quoted'=>'bg-indigo-100 text-indigo-700',
                            'sent'=>'bg-violet-100 text-violet-700','customer_responded'=>'bg-cyan-100 text-cyan-700',
                            'approved'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700',
                            'closed'=>'bg-gray-100 text-gray-700',
                        ];
                        $statusLabels = \App\Models\QuoteRequest::$statuses;
                    @endphp
                    <div class="flex items-start gap-2 text-xs">
                        <div class="mt-1 w-2 h-2 rounded-full bg-brand-red shrink-0"></div>
                        <div>
                            <span class="px-1.5 py-0.5 rounded text-xs font-bold {{ $statusColors[$log->to_status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$log->to_status] ?? $log->to_status }}
                            </span>
                            <span class="text-gray-400 ml-1">{{ $log->created_at->format('d M, H:i') }}</span>
                            @if($log->changedBy)<span class="text-gray-400">· {{ $log->changedBy->name }}</span>@endif
                            @if($log->note)<p class="text-gray-500 mt-0.5 italic">{{ $log->note }}</p>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- end right col --}}
</div>

{{-- ====== ALPINE JS LOGIC ====== --}}
<script>
function quotationBuilder() {
    return {
        items: @json($initialItems),

        meta: {
            valid_until: '{{ optional($latestQuotation?->valid_until)->format('Y-m-d') ?? now()->addDays(14)->format('Y-m-d') }}',
            discount_amount: {{ (float)($latestQuotation?->discount_amount ?? 0) }},
            tax_amount: {{ (float)($latestQuotation?->tax_amount ?? 0) }},
            payment_terms: @json($latestQuotation?->payment_terms ?? ''),
            delivery_details: @json($latestQuotation?->delivery_details ?? ''),
            terms_conditions: @json($latestQuotation?->terms_conditions ?? ''),
            notes: @json($latestQuotation?->notes ?? ''),
        },

        quotationId: {{ $latestQuotation?->id ?? 'null' }},
        quotationNumber: @json($latestQuotation?->quote_number ?? ''),
        publicToken: @json($latestQuotation?->public_token ?? ''),
        qtStatus: @json($latestQuotation?->status ?? ''),

        saving: false,
        saveError: '',
        sendingWa: false,
        waResult: '',
        waSuccess: false,
        waLogs: @json($waLogsInitial),
        showPreview: {{ $latestQuotation ? 'true' : 'false' }},

        init() {},

        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: 0, discount_percent: 0, item_notes: '' });
        },

        lineSubtotal(item) {
            const base = (item.quantity || 0) * (item.unit_price || 0);
            return base * (1 - (item.discount_percent || 0) / 100);
        },

        subtotal() {
            return this.items.reduce((s, i) => s + this.lineSubtotal(i), 0);
        },

        grandTotal() {
            return this.subtotal() - (this.meta.discount_amount || 0) + (this.meta.tax_amount || 0);
        },

        async saveQuotation() {
            this.saving = true;
            this.saveError = '';
            const body = {
                _token: '{{ csrf_token() }}',
                ...this.meta,
                items: this.items.map((item, idx) => ({ ...item, sort_order: idx })),
            };

            try {
                const res = await fetch('{{ route('admin.quote-requests.save-quotation', $quoteRequest) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json();
                if (res.ok) {
                    this.quotationId = data.id;
                    this.quotationNumber = data.quote_number;
                    this.publicToken = data.public_token;
                    this.qtStatus = data.status;
                    this.showPreview = true;
                    this.showSuccessToast('✓ Quotation saved successfully!');
                } else {
                    this.saveError = data.message || 'Failed to save quotation.';
                }
            } catch(e) {
                this.saveError = 'Network error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async sendWhatsApp(force = false) {
            if (!this.quotationId) {
                alert('Please save the quotation first before sending.');
                return;
            }
            if (!force && !confirm('Send quotation to {{ $quoteRequest->phone }} via WhatsApp?')) return;

            this.sendingWa = true;
            this.waResult = '';
            try {
                const res = await fetch('{{ route('admin.quote-requests.send-whatsapp', $quoteRequest) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(force ? { force: true, _token: '{{ csrf_token() }}' } : { _token: '{{ csrf_token() }}' }),
                });
                const data = await res.json();
                if (res.status === 409) {
                    if (confirm('Already sent recently. Force resend?')) {
                        this.sendingWa = false;
                        return this.sendWhatsApp(true);
                    }
                } else if (data.success) {
                    this.waSuccess = true;
                    this.waResult = '✓ Quotation sent to {{ $quoteRequest->phone }} via WhatsApp!';
                    this.qtStatus = 'sent';
                    this.waLogs.unshift({ id: Date.now(), success: true, sent_at: 'Just now' });
                } else {
                    this.waSuccess = false;
                    this.waResult = '✗ Failed: ' + (data.message || 'WhatsApp API error');
                }
            } catch(e) {
                this.waSuccess = false;
                this.waResult = '✗ Network error. Check connection.';
            } finally {
                this.sendingWa = false;
            }
        },

        printQuotation() {
            this.showPreview = true;
            setTimeout(() => window.print(), 300);
        },

        showSuccessToast(msg) {
            const el = document.createElement('div');
            el.className = 'fixed top-5 right-5 z-50 bg-green-600 text-white text-sm font-bold px-5 py-3 rounded-xl shadow-xl';
            el.textContent = msg;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3500);
        }
    }
}
</script>

{{-- Print styles --}}
<style>
@media print {
    body * { visibility: hidden !important; }
    #quotation-print-area, #quotation-print-area * { visibility: visible !important; }
    #quotation-print-area {
        position: fixed !important;
        left: 0; top: 0;
        width: 100%; height: auto;
        background: white;
        padding: 40px;
    }
    @page { margin: 15mm; }
}
</style>
@endsection
