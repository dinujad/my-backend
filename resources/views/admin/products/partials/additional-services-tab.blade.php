<div class="space-y-4">
    <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg">
        <h3 class="font-semibold text-gray-800">Optional add-on services</h3>
        <p class="text-xs text-gray-500 mt-1">Customers can tick extras on the product page. Choose whether each fee applies per item or once per order.</p>
    </div>
    <div class="space-y-3">
        <template x-for="(svc, index) in additionalServices" :key="index">
            <div class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm space-y-3">
                <div class="flex justify-between items-center">
                    <span class="font-bold text-sm text-gray-700" x-text="'Service #' + (index + 1)"></span>
                    <button type="button" @click="additionalServices.splice(index, 1)" class="text-red-400 hover:text-red-600 text-xs font-semibold"><i class="bi bi-trash"></i> Remove</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Service name *</label>
                        <input type="text" :name="`additional_services[${index}][name]`" x-model="svc.name" required class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none" placeholder="e.g. Design together">
                    </div>
                    <div>
                        <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Price (Rs.) *</label>
                        <input type="number" step="0.01" min="0" :name="`additional_services[${index}][price]`" x-model.number="svc.price" required class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Charge as</label>
                        <select :name="`additional_services[${index}][pricing_type]`" x-model="svc.pricing_type" class="w-full max-w-md border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none bg-white">
                            <option value="per_item">Per item (multiplied by quantity)</option>
                            <option value="per_order">Once per order / project</option>
                        </select>
                        <p class="mt-1 text-[10px] text-gray-400">Example: qty 3 + Rs. 200 once per order adds Rs. 200 only (not Rs. 600).</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Short description (optional)</label>
                        <input type="text" :name="`additional_services[${index}][description]`" x-model="svc.description" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none" placeholder="Shown under the checkbox on product page">
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="hidden" :name="`additional_services[${index}][is_active]`" value="0">
                            <input type="checkbox" :name="`additional_services[${index}][is_active]`" value="1" x-model="svc.is_active" class="rounded border-gray-300 text-brand-red focus:ring-brand-red">
                            Active (visible on storefront)
                        </label>
                    </div>
                </div>
            </div>
        </template>
        <button type="button" @click="additionalServices.push({ name: '', description: '', price: 0, pricing_type: 'per_item', is_active: true })" class="w-full border-2 border-dashed border-gray-300 text-gray-500 hover:text-brand-red hover:border-brand-red rounded-lg py-3 text-sm font-semibold transition bg-gray-50 hover:bg-white">
            + Add additional service
        </button>
    </div>
</div>
