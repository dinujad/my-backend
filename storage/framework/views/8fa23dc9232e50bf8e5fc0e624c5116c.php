

<?php $__env->startSection('title', 'Blog Posts'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-journal-text text-brand-red"></i> Blog Posts
    </h1>
    <a href="<?php echo e(route('admin.blog.create')); ?>" class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-dark transition text-sm font-medium">
        <i class="bi bi-plus-lg"></i> New Post
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Title</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Category</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Author</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900"><?php echo e(Str::limit($post->title, 50)); ?></p>
                        <p class="text-xs text-gray-400 font-mono">/blog/<?php echo e($post->slug); ?></p>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($post->category?->name ?? '–'); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($post->author?->name ?? '–'); ?></td>
                    <td class="px-4 py-3 text-center">
                        <?php if($post->is_published): ?>
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Published</span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right flex items-center justify-end gap-3">
                        <a href="<?php echo e(route('admin.blog.edit', $post)); ?>" class="text-blue-500 hover:underline text-xs">Edit</a>
                        <form method="POST" action="<?php echo e(route('admin.blog.destroy', $post)); ?>" onsubmit="return confirm('Delete this post?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No blog posts yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if($posts->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($posts->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\blog\index.blade.php ENDPATH**/ ?>