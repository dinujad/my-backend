

<?php $__env->startSection('title', $customer->name); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center gap-3 mb-6">
    <a href="<?php echo e(route('admin.customers.index')); ?>" class="text-gray-500 hover:text-gray-800"><i class="bi bi-arrow-left"></i></a>
    <h1 class="text-2xl font-bold text-gray-900"><?php echo e($customer->name); ?></h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-14 h-14 bg-brand-red rounded-full flex items-center justify-center text-white text-xl font-bold">
                <?php echo e(strtoupper(substr($customer->name, 0, 1))); ?>

            </div>
            <div>
                <p class="font-semibold text-gray-900"><?php echo e($customer->name); ?></p>
                <p class="text-sm text-gray-500"><?php echo e($customer->email); ?></p>
            </div>
        </div>
        <div class="space-y-2 text-sm">
            <p class="flex items-center gap-2 text-gray-600"><i class="bi bi-telephone"></i> <?php echo e($customer->phone ?? 'No phone'); ?></p>
            <p class="flex items-center gap-2 text-gray-600"><i class="bi bi-calendar"></i> Joined <?php echo e($customer->created_at->format('d M Y')); ?></p>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-4">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-xl font-bold text-gray-900"><?php echo e($customer->orders->count()); ?></p>
                <p class="text-xs text-gray-500">Orders</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-xl font-bold text-gray-900"><?php echo e(number_format($customer->total_spent, 0)); ?></p>
                <p class="text-xs text-gray-500">Rs. Spent</p>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <a href="<?php echo e(route('admin.customers.edit', $customer)); ?>" class="flex-1 text-center bg-brand-red text-white py-2 rounded-lg text-sm hover:bg-red-dark transition">Edit</a>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Order History</div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Order #</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Status</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Total</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__empty_1 = true; $__currentLoopData = $customer->orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono">
                                <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="text-brand-red hover:underline"><?php echo e($order->order_number); ?></a>
                            </td>
                            <td class="px-4 py-3"><?php echo e(ucfirst($order->status)); ?></td>
                            <td class="px-4 py-3 text-right font-medium">Rs. <?php echo e(number_format($order->total, 2)); ?></td>
                            <td class="px-4 py-3 text-right text-gray-500"><?php echo e($order->created_at->format('d M Y')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No orders yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\customers\show.blade.php ENDPATH**/ ?>