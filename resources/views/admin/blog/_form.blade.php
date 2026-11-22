{{-- Shared create/edit form for blog posts. Expects $blog (BlogPost, may be new) and $categories. --}}
@php
    $frontendUrl = rtrim((string) config('app.frontend_url', 'https://printworks.lk'), '/') ?: 'https://printworks.lk';
    $initial = [
        'title' => old('title', $blog->title ?? ''),
        'slug' => old('slug', $blog->slug ?? ''),
        'slugTouched' => filled($blog->slug ?? null) || filled(old('slug')),
        'excerpt' => old('excerpt', $blog->excerpt ?? ''),
        'seoTitle' => old('seo_title', $blog->seo_title ?? ''),
        'seoDescription' => old('seo_description', $blog->seo_description ?? ''),
        'focusKeyword' => old('focus_keyword', $blog->focus_keyword ?? ''),
        'featuredImage' => old('featured_image', $blog->featured_image ?? ''),
        'featuredImageAlt' => old('featured_image_alt', $blog->featured_image_alt ?? ''),
        'faqs' => array_values(old('faqs', $blog->faqs ?? []) ?: []),
        'siteUrl' => $frontendUrl,
    ];
    $relatedLinksText = collect($blog->related_links ?? [])
        ->map(fn ($l) => trim(($l['label'] ?? '').' | '.($l['href'] ?? '')))->filter()->implode("\n");
    $publishedAt = old('published_at', $blog->published_at?->format('Y-m-d\TH:i'));
    $inputCls = 'w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red';
@endphp

@if ($errors->any())
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <p class="font-semibold mb-1">Please fix the following:</p>
        <ul class="list-disc pl-5 space-y-0.5">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div x-data="blogForm(@js($initial))" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    {{-- ───────────── Main column ───────────── --}}
    <div class="xl:col-span-2 space-y-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title (H1) <span class="text-red-500">*</span></label>
                <input name="title" x-model="title" @input="syncSlug()" required maxlength="255"
                       placeholder="e.g. Sticker Printing in Sri Lanka: A Complete Guide"
                       class="{{ $inputCls }} text-base font-medium">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                <div class="flex items-stretch rounded-lg border border-gray-200 focus-within:border-brand-red overflow-hidden">
                    <span class="bg-gray-50 px-3 flex items-center text-xs text-gray-500 border-r border-gray-200 whitespace-nowrap">{{ $frontendUrl }}/blog/</span>
                    <input name="slug" x-model="slug" @input="slugTouched = true" @blur="slug = slugify(slug)"
                           class="flex-1 px-3 py-2 text-sm font-mono focus:outline-none" placeholder="auto-generated-from-title">
                    <button type="button" @click="slugTouched = false; syncSlug()" title="Regenerate from title"
                            class="px-3 text-gray-500 hover:text-brand-red border-l border-gray-200"><i class="bi bi-arrow-repeat"></i></button>
                </div>
                <p class="mt-1 text-xs text-gray-400">Lowercase, hyphens only, short (3–6 words), include the focus keyword. Stop words (a, the, and…) are removed automatically.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt <span class="text-gray-400 font-normal">(shown on blog cards &amp; under the H1)</span></label>
                <textarea name="excerpt" x-model="excerpt" rows="2" maxlength="500" class="{{ $inputCls }}"
                          placeholder="One or two sentences summarising the post..."></textarea>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Content <span class="text-red-500">*</span></label>
                    <span class="text-xs text-gray-400"><span x-text="stats.words"></span> words · <span x-text="Math.max(1, Math.ceil(stats.words / 200))"></span> min read</span>
                </div>
                <textarea id="blog-content" name="content" rows="20">{{ old('content', $blog->content ?? '') }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Use Heading 2 / Heading 3 for sections (the article title is already the H1). Images uploaded here are saved to the Media Library — always add alt text.</p>
            </div>
        </div>

        {{-- SEO settings --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="bi bi-search text-brand-red"></i> SEO Settings
            </h2>

            {{-- Google snippet preview --}}
            <div class="mb-5 rounded-lg border border-gray-200 p-4 bg-gray-50">
                <p class="text-[11px] uppercase tracking-wider text-gray-400 mb-2">Google preview</p>
                <p class="text-xs text-gray-600 truncate" x-text="siteUrl.replace(/^https?:\/\//, '') + ' › blog › ' + (slug || 'your-post')"></p>
                <p class="text-[18px] leading-snug text-[#1a0dab] truncate" x-text="(seoTitle || title || 'Your post title') + ' | Print Works.LK'"></p>
                <p class="text-[13px] text-gray-600 line-clamp-2" x-text="seoDescription || excerpt || 'Write a meta description that makes people want to click.'"></p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Focus keyword</label>
                    <input name="focus_keyword" x-model="focusKeyword" maxlength="100" class="{{ $inputCls }}"
                           placeholder="e.g. sticker printing sri lanka">
                    <p class="mt-1 text-xs text-gray-400">The main search phrase this post should rank for. Used by the SEO checklist.</p>
                </div>
                <div>
                    <div class="flex justify-between mb-1">
                        <label class="text-xs font-medium text-gray-600">SEO title</label>
                        <span class="text-xs" :class="lenClass(seoTitle.length, 30, 60)"><span x-text="seoTitle.length"></span>/60</span>
                    </div>
                    <input name="seo_title" x-model="seoTitle" maxlength="70" class="{{ $inputCls }}"
                           :placeholder="title || 'Defaults to the post title'">
                    <div class="mt-1 h-1 rounded bg-gray-100 overflow-hidden"><div class="h-full transition-all" :class="barClass(seoTitle.length, 30, 60)" :style="'width:' + Math.min(100, seoTitle.length / 60 * 100) + '%'"></div></div>
                </div>
                <div>
                    <div class="flex justify-between mb-1">
                        <label class="text-xs font-medium text-gray-600">Meta description</label>
                        <span class="text-xs" :class="lenClass(seoDescription.length, 120, 160)"><span x-text="seoDescription.length"></span>/160</span>
                    </div>
                    <textarea name="seo_description" x-model="seoDescription" rows="3" maxlength="170" class="{{ $inputCls }}"
                              placeholder="120–160 characters. Include the focus keyword and a reason to click."></textarea>
                    <div class="mt-1 h-1 rounded bg-gray-100 overflow-hidden"><div class="h-full transition-all" :class="barClass(seoDescription.length, 120, 160)" :style="'width:' + Math.min(100, seoDescription.length / 160 * 100) + '%'"></div></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Meta keywords <span class="text-gray-400">(comma separated)</span></label>
                        <input name="meta_keywords" value="{{ old('meta_keywords', $blog->meta_keywords ?? '') }}" maxlength="255" class="{{ $inputCls }}"
                               placeholder="stickers, vinyl stickers, label printing">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tags <span class="text-gray-400">(comma separated)</span></label>
                        <input name="tags_text" value="{{ old('tags_text', implode(', ', $blog->tags ?? [])) }}" maxlength="500" class="{{ $inputCls }}"
                               placeholder="Stickers, Branding">
                    </div>
                </div>
                <details class="group">
                    <summary class="cursor-pointer text-xs font-semibold text-gray-600 select-none">Advanced (canonical, social image, indexing)</summary>
                    <div class="mt-3 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Canonical URL <span class="text-gray-400">(leave empty to use this post's URL)</span></label>
                            <input name="canonical_url" type="url" value="{{ old('canonical_url', $blog->canonical_url ?? '') }}" class="{{ $inputCls }}" placeholder="https://...">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Social share image URL <span class="text-gray-400">(Facebook / WhatsApp; defaults to featured image, 1200×630 ideal)</span></label>
                            <input name="og_image" value="{{ old('og_image', $blog->og_image ?? '') }}" class="{{ $inputCls }}" placeholder="https://...">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="noindex" value="0">
                            <input type="checkbox" name="noindex" value="1" {{ old('noindex', $blog->noindex ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-red">
                            <span class="text-sm text-gray-700">Hide from Google (noindex)</span>
                        </label>
                    </div>
                </details>
            </div>
        </div>

        {{-- FAQs --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-1">
                <h2 class="font-semibold text-gray-800 flex items-center gap-2"><i class="bi bi-question-circle text-brand-red"></i> FAQs</h2>
                <button type="button" @click="addFaq()" x-show="faqs.length < 10"
                        class="text-xs font-medium text-brand-red hover:underline"><i class="bi bi-plus-lg"></i> Add question</button>
            </div>
            <p class="text-xs text-gray-400 mb-4">Shown at the end of the post and added as FAQ schema for Google rich results. Max 10.</p>
            <div class="space-y-3">
                <template x-for="(faq, i) in faqs" :key="i">
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 space-y-2">
                        <div class="flex gap-2">
                            <input :name="'faqs[' + i + '][question]'" x-model="faq.question" maxlength="255" placeholder="Question"
                                   class="{{ $inputCls }} bg-white">
                            <button type="button" @click="faqs.splice(i, 1)" class="text-gray-400 hover:text-red-500 px-2" title="Remove"><i class="bi bi-trash"></i></button>
                        </div>
                        <textarea :name="'faqs[' + i + '][answer]'" x-model="faq.answer" rows="2" maxlength="1000" placeholder="Answer"
                                  class="{{ $inputCls }} bg-white"></textarea>
                    </div>
                </template>
                <p x-show="faqs.length === 0" class="text-sm text-gray-400">No FAQs yet.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-800 mb-1 flex items-center gap-2"><i class="bi bi-link-45deg text-brand-red"></i> Related Links</h2>
            <p class="text-xs text-gray-400 mb-3">Internal links to products / categories. One per line: <code>Label | /path</code></p>
            <textarea name="related_links_text" rows="4" placeholder="Acrylic name badges | /product/acrylic-name-badge" class="{{ $inputCls }} font-mono">{{ old('related_links_text', $relatedLinksText) }}</textarea>
        </div>
    </div>

    {{-- ───────────── Sidebar ───────────── --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4 xl:sticky xl:top-4">
            <h2 class="font-semibold text-gray-800">Publish</h2>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $blog->is_published ?? false) ? 'checked' : '' }} class="rounded border-gray-300 text-brand-red">
                <span class="text-sm text-gray-700">Published</span>
            </label>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Publish date</label>
                <input type="datetime-local" name="published_at" value="{{ $publishedAt }}" class="{{ $inputCls }}">
                <p class="mt-1 text-xs text-gray-400">Leave empty to use the time you publish.</p>
            </div>
            @if ($blog->exists && $blog->is_published)
                <a href="{{ $frontendUrl }}/blog/{{ $blog->slug }}" target="_blank" rel="noopener"
                   class="flex items-center gap-1 text-xs text-blue-600 hover:underline"><i class="bi bi-box-arrow-up-right"></i> View live post</a>
            @endif
            <button type="submit" class="w-full bg-brand-red text-white py-2.5 rounded-lg font-medium hover:bg-red-dark transition">
                {{ $blog->exists ? 'Save Changes' : 'Save Post' }}
            </button>

            {{-- SEO score --}}
            <div class="border-t border-gray-100 pt-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-800">SEO score</h3>
                    <span class="text-sm font-bold px-2 py-0.5 rounded"
                          :class="score >= 80 ? 'bg-green-100 text-green-700' : (score >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700')"
                          x-text="score + '/100'"></span>
                </div>
                <ul class="space-y-1.5">
                    <template x-for="c in checks" :key="c.label">
                        <li class="flex items-start gap-2 text-xs">
                            <i class="bi mt-0.5" :class="c.ok ? 'bi-check-circle-fill text-green-500' : 'bi-x-circle-fill text-red-400'"></i>
                            <span :class="c.ok ? 'text-gray-600' : 'text-gray-800'" x-text="c.label"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
            <h2 class="font-semibold text-gray-800">Featured Image</h2>
            <div class="aspect-[16/9] rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 overflow-hidden flex items-center justify-center">
                <template x-if="featuredPreview || featuredImage">
                    <img :src="featuredPreview || featuredImage" :alt="featuredImageAlt" class="w-full h-full object-cover">
                </template>
                <template x-if="!(featuredPreview || featuredImage)">
                    <div class="text-center text-gray-400 text-xs"><i class="bi bi-image text-3xl block mb-1"></i>1200×675 recommended</div>
                </template>
            </div>
            <input type="file" name="featured_image_file" accept="image/jpeg,image/png,image/webp" @change="previewFeatured($event)"
                   class="w-full text-xs file:mr-3 file:border-0 file:rounded file:bg-gray-100 file:text-gray-700 file:text-xs file:px-2 file:py-1">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">…or image URL</label>
                <input name="featured_image" x-model="featuredImage" class="{{ $inputCls }}" placeholder="https://...">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Alt text <span class="text-gray-400">(describe the image, include keyword naturally)</span></label>
                <input name="featured_image_alt" x-model="featuredImageAlt" maxlength="255" class="{{ $inputCls }}">
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3" x-data="{ adding: false, newCat: '' }">
            <h2 class="font-semibold text-gray-800">Category</h2>
            <select name="blog_category_id" id="blog-category" class="{{ $inputCls }}">
                <option value="">Uncategorised</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ (string) old('blog_category_id', $blog->blog_category_id ?? '') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="button" x-show="!adding" @click="adding = true" class="text-xs text-brand-red hover:underline"><i class="bi bi-plus-lg"></i> New category</button>
            <div x-show="adding" class="flex gap-2">
                <input x-model="newCat" @keydown.enter.prevent="addCategory(newCat).then(ok => { if (ok) { newCat = ''; adding = false; } })"
                       class="{{ $inputCls }}" placeholder="Category name">
                <button type="button" @click="addCategory(newCat).then(ok => { if (ok) { newCat = ''; adding = false; } })"
                        class="px-3 rounded-lg bg-gray-900 text-white text-xs">Add</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js" referrerpolicy="origin"></script>
<script>
const STOP_WORDS = ['a','an','the','and','or','of','to','in','on','for','with','at','by','is','are'];

function blogForm(initial) {
    return {
        ...initial,
        faqs: (initial.faqs || []).map(f => ({ question: f.question || '', answer: f.answer || '' })),
        featuredPreview: '',
        html: '',
        stats: { words: 0, h2: 0, imgs: 0, imgsNoAlt: 0, internalLinks: 0, firstPara: '', text: '' },

        init() {
            this.syncSlug();
            const self = this;
            tinymce.init({
                selector: '#blog-content',
                license_key: 'gpl',
                height: 620,
                menubar: 'edit view insert format table tools',
                plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table wordcount autoresize',
                toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright | bullist numlist outdent indent | link image media table | blockquote hr | removeformat code fullscreen',
                block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Quote=blockquote',
                autoresize_bottom_margin: 40,
                min_height: 500,
                max_height: 1200,
                branding: false,
                promotion: false,
                convert_urls: false,
                image_caption: true,
                image_advtab: true,
                image_title: true,
                a11y_advanced_options: true,
                link_default_target: '',
                link_assume_external_targets: false,
                link_rel_list: [
                    { title: 'None', value: '' },
                    { title: 'nofollow', value: 'nofollow noopener' },
                    { title: 'sponsored', value: 'sponsored nofollow noopener' },
                ],
                automatic_uploads: true,
                images_file_types: 'jpg,jpeg,png,gif,webp',
                images_upload_handler: (blobInfo) => new Promise((resolve, reject) => {
                    const fd = new FormData();
                    fd.append('file', blobInfo.blob(), blobInfo.filename());
                    fetch(@js(route('admin.blog.upload-image')), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json' },
                        body: fd,
                    }).then(r => r.json().then(j => ({ ok: r.ok, j })))
                      .then(({ ok, j }) => ok && j.location ? resolve(j.location) : reject({ message: j.message || 'Upload failed', remove: true }))
                      .catch(() => reject({ message: 'Upload failed', remove: true }));
                }),
                content_style: 'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:15px;line-height:1.7;color:#374151;max-width:760px;margin:16px auto;padding:0 12px} h2{font-size:1.4em;color:#111827;margin-top:1.6em} h3{font-size:1.15em;color:#111827} img{max-width:100%;height:auto;border-radius:10px} blockquote{border-left:4px solid #FF1F40;margin-left:0;padding-left:16px;color:#4b5563} a{color:#FF1F40} table{border-collapse:collapse} td,th{border:1px solid #e5e7eb;padding:6px 10px}',
                setup(editor) {
                    const update = () => self.analyse(editor.getContent());
                    editor.on('init', update);
                    editor.on('input change undo redo SetContent keyup', update);
                },
            });
            this.$watch('focusKeyword', () => this.analyse(this.html));
        },

        slugify(value) {
            const words = (value || '').toString().normalize('NFKD').replace(/[̀-ͯ]/g, '')
                .toLowerCase().replace(/&/g, ' and ').replace(/[^a-z0-9\s-]/g, ' ')
                .trim().split(/[\s-]+/).filter(Boolean);
            const filtered = words.filter(w => !STOP_WORDS.includes(w));
            let slug = (filtered.length ? filtered : words).join('-');
            if (slug.length > 75) slug = slug.slice(0, 75).replace(/-[^-]*$/, '') || slug.slice(0, 75);
            return slug.replace(/^-+|-+$/g, '');
        },

        syncSlug() {
            if (!this.slugTouched) this.slug = this.slugify(this.title);
        },

        addFaq() {
            if (this.faqs.length < 10) this.faqs.push({ question: '', answer: '' });
        },

        previewFeatured(e) {
            const file = e.target.files[0];
            this.featuredPreview = file ? URL.createObjectURL(file) : '';
            if (file && !this.featuredImageAlt) this.featuredImageAlt = this.title;
        },

        async addCategory(name) {
            if (!name || !name.trim()) return false;
            const res = await fetch(@js(route('admin.blog.categories.store')), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: name.trim() }),
            });
            const j = await res.json();
            if (!res.ok) { alert(j.message || 'Could not add category'); return false; }
            const sel = document.getElementById('blog-category');
            sel.add(new Option(j.name, j.id, true, true));
            return true;
        },

        analyse(html) {
            this.html = html || '';
            const doc = new DOMParser().parseFromString('<div>' + this.html + '</div>', 'text/html');
            const text = (doc.body.textContent || '').replace(/\s+/g, ' ').trim();
            const imgs = [...doc.querySelectorAll('img')];
            const links = [...doc.querySelectorAll('a[href]')];
            const firstP = doc.querySelector('p');
            this.stats = {
                words: text ? text.split(' ').length : 0,
                h2: doc.querySelectorAll('h2').length,
                imgs: imgs.length,
                imgsNoAlt: imgs.filter(i => !(i.getAttribute('alt') || '').trim()).length,
                internalLinks: links.filter(a => { const h = a.getAttribute('href'); return h.startsWith('/') || h.includes('printworks.lk'); }).length,
                firstPara: (firstP ? firstP.textContent : text.slice(0, 300)).toLowerCase(),
                text: text.toLowerCase(),
            };
        },

        get kw() { return (this.focusKeyword || '').trim().toLowerCase(); },

        get density() {
            if (!this.kw || !this.stats.words) return 0;
            const re = new RegExp(this.kw.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
            const hits = (this.stats.text.match(re) || []).length;
            return hits * this.kw.split(/\s+/).length / this.stats.words * 100;
        },

        get checks() {
            const kw = this.kw;
            const title = (this.seoTitle || this.title || '').toLowerCase();
            const desc = (this.seoDescription || this.excerpt || '');
            const t = (this.seoTitle || this.title || '').length;
            const kwSlug = this.slugify(kw);
            return [
                { label: 'Focus keyword is set', ok: !!kw },
                { label: 'Focus keyword in SEO title', ok: !!kw && title.includes(kw) },
                { label: 'Focus keyword in meta description', ok: !!kw && desc.toLowerCase().includes(kw) },
                { label: 'Focus keyword in URL slug', ok: !!kwSlug && (this.slug || '').includes(kwSlug) },
                { label: 'Focus keyword in first paragraph', ok: !!kw && this.stats.firstPara.includes(kw) },
                { label: 'Keyword density 0.5–2.5% (now ' + this.density.toFixed(1) + '%)', ok: this.density >= 0.5 && this.density <= 2.5 },
                { label: 'SEO title 30–60 characters', ok: t >= 30 && t <= 60 },
                { label: 'Meta description 120–160 characters', ok: desc.length >= 120 && desc.length <= 160 },
                { label: 'At least 600 words (now ' + this.stats.words + ')', ok: this.stats.words >= 600 },
                { label: 'Uses H2 subheadings', ok: this.stats.h2 >= 2 },
                { label: 'Has internal links to products/pages', ok: this.stats.internalLinks >= 1 },
                { label: 'All content images have alt text', ok: this.stats.imgsNoAlt === 0 },
                { label: 'Featured image with alt text', ok: !!(this.featuredImage || this.featuredPreview) && !!this.featuredImageAlt.trim() },
                { label: 'Slug is short (≤ 60 chars)', ok: !!this.slug && this.slug.length <= 60 },
            ];
        },

        get score() {
            const c = this.checks;
            return Math.round(c.filter(x => x.ok).length / c.length * 100);
        },

        lenClass(n, min, max) {
            if (n === 0) return 'text-gray-400';
            return n >= min && n <= max ? 'text-green-600' : 'text-amber-600';
        },
        barClass(n, min, max) {
            return n >= min && n <= max ? 'bg-green-500' : (n > max ? 'bg-red-400' : 'bg-amber-400');
        },
    };
}
</script>
