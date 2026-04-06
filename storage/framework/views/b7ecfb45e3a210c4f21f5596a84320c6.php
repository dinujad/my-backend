

<?php $__env->startSection('title', 'SEO Settings'); ?>

<?php $__env->startSection('content'); ?>
<h1 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
    <i class="bi bi-search text-brand-red"></i> SEO Settings
</h1>

<form method="POST" action="<?php echo e(route('admin.seo.update')); ?>">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
            <h2 class="font-semibold text-gray-800 border-b border-gray-100 pb-3">General SEO</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site Title <span class="text-gray-400 font-normal text-xs">(max 60 chars)</span></label>
                <input name="site_title" value="<?php echo e($settings['site_title']); ?>" maxlength="60"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                <p class="text-xs text-gray-400 mt-1">Used in browser tab and search results.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site Description <span class="text-gray-400 font-normal text-xs">(max 160 chars)</span></label>
                <textarea name="site_description" rows="3" maxlength="160"
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red"><?php echo e($settings['site_description']); ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
                <input name="site_keywords" value="<?php echo e($settings['site_keywords']); ?>" placeholder="printing, acrylic, custom..."
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Canonical Base URL</label>
                <input name="canonical_base_url" value="<?php echo e($settings['canonical_base_url']); ?>" placeholder="https://printworks.lk"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red font-mono">
            </div>
        </div>

        
        <div class="space-y-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                <h2 class="font-semibold text-gray-800 border-b border-gray-100 pb-3">Social & OG Image</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default OG Image URL</label>
                    <input name="og_image" value="<?php echo e($settings['og_image']); ?>" placeholder="https://..."
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                    <p class="text-xs text-gray-400 mt-1">Shown when sharing on Facebook / WhatsApp.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Twitter Handle</label>
                    <input name="twitter_handle" value="<?php echo e($settings['twitter_handle']); ?>" placeholder="@printworks_lk"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Facebook Page URL</label>
                    <input name="facebook_page" value="<?php echo e($settings['facebook_page']); ?>" placeholder="https://facebook.com/..."
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                <h2 class="font-semibold text-gray-800 border-b border-gray-100 pb-3">Analytics & Verification</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Google Analytics ID</label>
                    <input name="google_analytics_id" value="<?php echo e($settings['google_analytics_id']); ?>" placeholder="G-XXXXXXXX"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Google Search Console Verification</label>
                    <input name="google_search_console" value="<?php echo e($settings['google_search_console']); ?>" placeholder="meta content value..."
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red font-mono">
                </div>
            </div>
        </div>

        
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-800 border-b border-gray-100 pb-3 mb-4">Custom robots.txt Content</h2>
            <textarea name="robots_txt" rows="8" placeholder="User-agent: *&#10;Allow: /&#10;Disallow: /cart"
                      class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red font-mono"><?php echo e($settings['robots_txt']); ?></textarea>
            <p class="text-xs text-gray-400 mt-1">Leave blank to use the default robots.txt.</p>
        </div>
    </div>

    <div class="mt-6">
        <button type="submit" class="flex items-center gap-2 bg-brand-red text-white px-6 py-2.5 rounded-lg font-medium hover:bg-red-dark transition">
            <i class="bi bi-save"></i> Save SEO Settings
        </button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\seo\index.blade.php ENDPATH**/ ?>