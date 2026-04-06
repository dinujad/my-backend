

<?php $__env->startSection('title', 'Edit Post'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center gap-3 mb-6">
    <a href="<?php echo e(route('admin.blog.index')); ?>" class="text-gray-500 hover:text-gray-800"><i class="bi bi-arrow-left"></i></a>
    <h1 class="text-2xl font-bold text-gray-900">Edit Post</h1>
</div>

<form method="POST" action="<?php echo e(route('admin.blog.update', $blog)); ?>">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input name="title" value="<?php echo e(old('title', $blog->title)); ?>" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input name="slug" value="<?php echo e(old('slug', $blog->slug)); ?>"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                    <textarea name="excerpt" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red"><?php echo e(old('excerpt', $blog->excerpt)); ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" rows="14" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red font-mono"><?php echo e(old('content', $blog->content)); ?></textarea>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="bi bi-search text-brand-red"></i> SEO Settings
                </h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">SEO Title</label>
                        <input name="seo_title" value="<?php echo e(old('seo_title', $blog->seo_title)); ?>" maxlength="60"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">SEO Description</label>
                        <textarea name="seo_description" rows="2" maxlength="160" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red"><?php echo e(old('seo_description', $blog->seo_description)); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
                <h2 class="font-semibold text-gray-800">Status</h2>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" <?php echo e($blog->is_published ? 'checked' : ''); ?> class="rounded border-gray-300 text-brand-red">
                    <span class="text-sm text-gray-700">Published</span>
                </label>
                <?php if($blog->published_at): ?>
                    <p class="text-xs text-gray-400">Published <?php echo e($blog->published_at->format('d M Y')); ?></p>
                <?php endif; ?>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
                <h2 class="font-semibold text-gray-800">Category</h2>
                <select name="blog_category_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                    <option value="">Uncategorised</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat->id); ?>" <?php echo e($blog->blog_category_id == $cat->id ? 'selected' : ''); ?>><?php echo e($cat->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Featured Image URL</label>
                    <input name="featured_image" value="<?php echo e(old('featured_image', $blog->featured_image)); ?>"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
                </div>
            </div>
            <button type="submit" class="w-full bg-brand-red text-white py-2.5 rounded-lg font-medium hover:bg-red-dark transition">Save Changes</button>
        </div>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\blog\edit.blade.php ENDPATH**/ ?>