

<?php $__env->startSection('title', 'Products'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-box-seam text-brand-red"></i> Products
    </h1>
    <a href="<?php echo e(route('admin.products.create')); ?>" class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-dark transition text-sm font-medium">
        <i class="bi bi-plus-lg"></i> Add Product
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Product</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Category</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">SKU</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Price</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <?php if($product->image): ?>
                                <img src="<?php echo e($product->image); ?>" alt="<?php echo e($product->name); ?>" class="w-9 h-9 rounded-lg object-cover bg-gray-100" onerror="this.remove()">
                            <?php else: ?>
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <i class="bi bi-image text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="font-medium text-gray-900"><?php echo e($product->name); ?></p>
                                <p class="text-xs text-gray-400 font-mono"><?php echo e($product->slug); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($product->category?->name ?? '–'); ?></td>
                    <td class="px-4 py-3 font-mono text-gray-500 text-xs"><?php echo e($product->sku ?? '–'); ?></td>
                    <td class="px-4 py-3 text-right font-medium text-gray-900">Rs. <?php echo e(number_format($product->price, 2)); ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo e($product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                            <?php echo e($product->is_active ? 'Active' : 'Inactive'); ?>

                        </span>
                        <?php if($product->is_featured): ?>
                            <span class="ml-1 px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">Featured</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="inline-flex items-center gap-1 text-brand-red hover:underline text-xs">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No products yet. <a href="<?php echo e(route('admin.products.create')); ?>" class="text-brand-red hover:underline">Add one</a>.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if($products->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($products->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\products\index.blade.php ENDPATH**/ ?>