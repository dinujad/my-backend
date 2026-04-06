

<?php $__env->startSection('title', 'Media Library'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-images text-brand-red"></i> Media Library
    </h1>
</div>


<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <h2 class="font-semibold text-gray-800 mb-4">Upload File</h2>
    <form method="POST" action="<?php echo e(route('admin.media.store')); ?>" enctype="multipart/form-data" class="flex flex-wrap gap-3 items-end">
        <?php echo csrf_field(); ?>
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-600 mb-1">Choose File</label>
            <input type="file" name="file" required accept="image/*,.pdf,.svg"
                   class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-brand-red file:mr-3 file:border-0 file:rounded file:bg-gray-100 file:text-gray-700 file:text-xs file:px-2 file:py-1">
        </div>
        <div class="flex-1 min-w-36">
            <label class="block text-xs font-medium text-gray-600 mb-1">Alt Text</label>
            <input name="alt_text" placeholder="Describe the image for SEO"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
        </div>
        <button type="submit" class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-dark transition">
            <i class="bi bi-cloud-upload"></i> Upload
        </button>
    </form>
</div>


<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
    <?php $__empty_1 = true; $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="group bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition">
            <div class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
                <?php if(str_starts_with($file->mime_type ?? '', 'image/')): ?>
                    <img src="<?php echo e(asset('storage/' . $file->path)); ?>" alt="<?php echo e($file->alt_text); ?>"
                         class="w-full h-full object-cover">
                <?php else: ?>
                    <i class="bi bi-file-earmark text-gray-400 text-3xl"></i>
                <?php endif; ?>
            </div>
            <div class="p-2">
                <p class="text-xs text-gray-700 truncate font-medium"><?php echo e($file->filename); ?></p>
                <p class="text-xs text-gray-400"><?php echo e($file->alt_text ? Str::limit($file->alt_text, 20) : 'No alt text'); ?></p>
                <div class="flex items-center gap-1 mt-1">
                    <button onclick="copyUrl('<?php echo e(asset('storage/' . $file->path)); ?>')"
                            class="text-xs text-blue-500 hover:underline">Copy URL</button>
                    <span class="text-gray-300">|</span>
                    <form method="POST" action="<?php echo e(route('admin.media.destroy', $file)); ?>" onsubmit="return confirm('Delete file?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-span-full py-16 text-center text-gray-400">
            <i class="bi bi-images text-5xl block mb-3"></i>
            <p>No files uploaded yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php if($files->hasPages()): ?>
    <div class="mt-6"><?php echo e($files->links()); ?></div>
<?php endif; ?>

<script>
function copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => alert('URL copied!'));
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\media\index.blade.php ENDPATH**/ ?>