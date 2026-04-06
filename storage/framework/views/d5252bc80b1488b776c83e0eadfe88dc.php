

<?php $__env->startSection('title', 'Customers'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-people text-brand-red"></i> Customers
    </h1>
    <a href="<?php echo e(route('admin.customers.create')); ?>" class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-dark transition text-sm font-medium">
        <i class="bi bi-plus-lg"></i> Add Customer
    </a>
</div>

<form method="GET" class="mb-4 flex gap-2">
    <input name="search" value="<?php echo e(request('search')); ?>" placeholder="Search by name or email..."
           class="border border-gray-200 rounded-lg px-4 py-2 text-sm flex-1 max-w-sm focus:outline-none focus:border-brand-red">
    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700 transition">
        <i class="bi bi-search"></i>
    </button>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Name</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Email</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Phone</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Orders</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Spent</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($customer->name); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($customer->email); ?></td>
                    <td class="px-4 py-3 text-gray-500"><?php echo e($customer->phone ?? '–'); ?></td>
                    <td class="px-4 py-3 text-center text-gray-700"><?php echo e($customer->orders_count); ?></td>
                    <td class="px-4 py-3 text-right text-gray-900 font-medium">Rs. <?php echo e(number_format($customer->total_spent, 0)); ?></td>
                    <td class="px-4 py-3 text-right">
                        <a href="<?php echo e(route('admin.customers.show', $customer)); ?>" class="text-brand-red hover:underline text-xs mr-2">View</a>
                        <a href="<?php echo e(route('admin.customers.edit', $customer)); ?>" class="text-blue-500 hover:underline text-xs">Edit</a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No customers found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if($customers->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($customers->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\customers\index.blade.php ENDPATH**/ ?>