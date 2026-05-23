@php
    $isEdit = isset($slide) && $slide->exists;
@endphp

<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Eyebrow (small red text)</label>
            <input type="text" name="eyebrow" value="{{ old('eyebrow', $slide->eyebrow) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Premium Quality Printing">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title line 1 *</label>
            <input type="text" name="title_line1" value="{{ old('title_line1', $slide->title_line1) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Make Every Detail">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title line 2</label>
            <input type="text" name="title_line2" value="{{ old('title_line2', $slide->title_line2) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Stand Out">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Highlight / offer text</label>
            <input type="text" name="highlight_text" value="{{ old('highlight_text', $slide->highlight_text) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="UP TO 40% OFF">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Short paragraph under the title">{{ old('description', $slide->description) }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Button text *</label>
                <input type="text" name="cta_label" value="{{ old('cta_label', $slide->cta_label) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Button link *</label>
                <input type="text" name="cta_url" value="{{ old('cta_url', $slide->cta_url) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="/products">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort order</label>
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $slide->sort_order ?? 0) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex items-end pb-2">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $slide->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-red">
                    Active (show on homepage)
                </label>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Slide image {{ $isEdit ? '(leave empty to keep current)' : '*' }}</label>
        @if($isEdit && $slide->image)
            <div class="mb-3 rounded-xl border border-gray-200 overflow-hidden bg-gray-50">
                <img src="{{ $slide->imageUrl() }}" alt="Current slide" class="w-full max-h-64 object-cover">
            </div>
        @endif
        <input type="file" name="image" accept="image/*" {{ $isEdit ? '' : 'required' }} class="w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-red file:px-4 file:py-2 file:text-white file:text-sm file:font-medium hover:file:bg-red-700">
        <p class="mt-2 text-xs text-gray-500">Recommended: wide image, min 1000×600px. Max 5MB.</p>
    </div>
</div>
