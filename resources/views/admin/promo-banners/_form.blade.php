@php
    $isEdit = isset($banner) && $banner->exists;
@endphp

<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Small heading (gray text) *</label>
            <input type="text" name="title" value="{{ old('title', $banner->title) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="INDUSTRY-GRADE">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bold line</label>
            <input type="text" name="bold_text" value="{{ old('bold_text', $banner->bold_text) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="UV FLATBED">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Text after bold (optional)</label>
            <input type="text" name="post_text" value="{{ old('post_text', $banner->post_text) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Second line</label>
            <input type="text" name="second_line" value="{{ old('second_line', $banner->second_line) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="PRINTING">
        </div>
        <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 space-y-3">
            <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                <input type="checkbox" name="has_discount" value="1" {{ old('has_discount', $banner->has_discount) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-red">
                Show discount badge (UP TO … %)
            </label>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount % number</label>
                <input type="text" name="discount_number" value="{{ old('discount_number', $banner->discount_number) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="20">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Button text (when no discount)</label>
            <input type="text" name="action_text" value="{{ old('action_text', $banner->action_text) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Shop now">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Link URL *</label>
            <input type="text" name="href" value="{{ old('href', $banner->href) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="/products?category=UV+Flatbed">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Image alt text</label>
            <input type="text" name="image_alt" value="{{ old('image_alt', $banner->image_alt) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort order</label>
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex items-end pb-2">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $banner->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-red">
                    Active (show on homepage)
                </label>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Banner image {{ $isEdit ? '(leave empty to keep current)' : '*' }}</label>
        @if($isEdit && $banner->image)
            <div class="mb-3 rounded-xl border border-gray-200 overflow-hidden bg-gray-50 p-4">
                <img src="{{ $banner->imageUrl() }}" alt="" class="max-h-40 w-full object-contain">
            </div>
        @endif
        <input type="file" name="image" accept="image/*" {{ $isEdit ? '' : 'required' }} class="w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-red file:px-4 file:py-2 file:text-white file:text-sm file:font-medium hover:file:bg-red-700">
        <p class="mt-2 text-xs text-gray-500">Square or product image on transparent/light background. Max 5MB.</p>
    </div>
</div>
