

<?php $__env->startSection('title', 'Orders'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-cart-check text-brand-red"></i> Orders
    </h1>
    <a href="<?php echo e(route('admin.orders.create')); ?>" class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-dark transition text-sm font-medium">
        <i class="bi bi-plus-lg"></i> New Order
    </a>
</div>


<div class="flex gap-2 mb-4 flex-wrap">
    <?php $__currentLoopData = ['', 'pending', 'processing', 'shipped', 'delivered', 'cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('admin.orders.index', $s ? ['status' => $s] : [])); ?>"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition <?php echo e(request('status', '') === $s ? 'bg-brand-red text-white' : 'bg-white text-gray-600 hover:bg-gray-100'); ?>">
            <?php echo e($s ? ucfirst($s) : 'All'); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Order #</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Customer</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Payment</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Total</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-700',
                        'processing' => 'bg-blue-100 text-blue-700',
                        'shipped' => 'bg-purple-100 text-purple-700',
                        'delivered' => 'bg-green-100 text-green-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ];
                    $paymentColors = [
                        'paid' => 'bg-green-100 text-green-700',
                        'unpaid' => 'bg-gray-100 text-gray-600',
                        'refunded' => 'bg-orange-100 text-orange-700',
                    ];
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-semibold text-gray-900"><?php echo e($order->order_number); ?></td>
                    <td class="px-4 py-3 text-gray-700"><?php echo e($order->customer?->name ?? 'Guest'); ?></td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo e($statusColors[$order->status] ?? 'bg-gray-100 text-gray-600'); ?>">
                            <?php echo e(ucfirst($order->status)); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo e($paymentColors[$order->payment_status] ?? 'bg-gray-100 text-gray-600'); ?>">
                            <?php echo e(ucfirst($order->payment_status)); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-900">Rs. <?php echo e(number_format($order->total, 2)); ?></td>
                    <td class="px-4 py-3 text-gray-500"><?php echo e($order->created_at->format('d M Y')); ?></td>
                    <td class="px-4 py-3 text-right">
                        <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="text-brand-red hover:underline text-xs font-medium">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No orders found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if($orders->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($orders->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\orders\index.blade.php ENDPATH**/ ?>