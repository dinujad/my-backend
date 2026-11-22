@php
    $faqRows = collect(old('faqs', old('faqs_present') ? [] : $savedFaqs))
        ->filter(fn ($row) => is_array($row))
        ->values()
        ->map(fn ($row, $index) => [
            'id' => $index,
            'question' => is_string($row['question'] ?? null) ? $row['question'] : '',
            'answer' => is_string($row['answer'] ?? null) ? $row['answer'] : '',
        ])->all();
@endphp

<section class="space-y-4 rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
    aria-labelledby="product-faq-heading"
    x-data="{ rows: @js($faqRows), nextId: {{ count($faqRows) }} }">
    <div>
        <h2 id="product-faq-heading" class="flex items-center gap-2 font-semibold text-gray-800">
            <i class="bi bi-question-circle text-brand-red" aria-hidden="true"></i>
            Frequently asked questions
        </h2>
        <p class="mt-1 text-sm text-gray-500">Optional. Add questions and answers to show on this product page.</p>
    </div>

    <input type="hidden" name="faqs_present" value="1">

    <template x-for="(row, index) in rows" :key="row.id">
        <fieldset class="space-y-3 rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between gap-3">
                <label :for="'product-faq-question-' + row.id" class="text-sm font-medium text-gray-700"
                    x-text="'Question ' + (index + 1)"></label>
                <button type="button" @click="rows.splice(index, 1)"
                    :aria-label="'Remove FAQ ' + (index + 1)"
                    class="text-sm font-medium text-brand-red hover:underline">
                    Remove
                </button>
            </div>
            <input type="text" :id="'product-faq-question-' + row.id" :name="'faqs[' + index + '][question]'"
                maxlength="255" x-model="row.question"
                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red">
            <label :for="'product-faq-answer-' + row.id" class="block text-sm font-medium text-gray-700">Answer</label>
            <textarea :id="'product-faq-answer-' + row.id" :name="'faqs[' + index + '][answer]'"
                rows="3" maxlength="1000" x-model="row.answer"
                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm outline-none focus:border-brand-red"></textarea>
        </fieldset>
    </template>

    <button type="button"
        @click="const id = nextId++; rows.push({ id, question: '', answer: '' }); $nextTick(() => document.getElementById('product-faq-question-' + id)?.focus())"
        class="inline-flex items-center gap-2 rounded-lg border border-brand-red/30 px-3 py-2 text-sm font-semibold text-brand-red hover:bg-red-50">
        <i class="bi bi-plus-lg" aria-hidden="true"></i>
        <span x-text="rows.length ? 'Add another FAQ' : 'Add FAQ'">Add FAQ</span>
    </button>
</section>
