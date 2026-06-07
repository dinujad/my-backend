<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Job title *</label>
            <input type="text" name="title" value="{{ old('title', $opening->title) }}" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">URL slug (optional)</label>
            <input type="text" name="slug" value="{{ old('slug', $opening->slug) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="auto-generated from title">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" name="location" value="{{ old('location', $opening->location ?? 'Biyagama') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employment type</label>
                <input type="text" name="employment_type" value="{{ old('employment_type', $opening->employment_type) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Full-time">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Short summary</label>
            <textarea name="summary" rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">{{ old('summary', $opening->summary) }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort order</label>
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $opening->sort_order ?? 0) }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 pb-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $opening->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-red">
                Active (show on careers page)
            </label>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Requirements (one per line)</label>
        <textarea name="requirements" rows="14" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono" placeholder="Must have valid certification...">{{ old('requirements', $opening->requirements) }}</textarea>
        <p class="mt-2 text-xs text-gray-500">Each line becomes a bullet point on the careers page.</p>
    </div>
</div>
