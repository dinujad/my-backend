

<?php $__env->startSection('title', 'Edit Customer'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center gap-3 mb-6">
    <a href="<?php echo e(route('admin.customers.index')); ?>" class="text-gray-500 hover:text-gray-800"><i class="bi bi-arrow-left"></i></a>
    <h1 class="text-2xl font-bold text-gray-900">Edit Customer</h1>
</div>

<div class="max-w-lg">
    <form method="POST" action="<?php echo e(route('admin.customers.update', $customer)); ?>" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input name="name" value="<?php echo e(old('name', $customer->name)); ?>" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input name="email" type="email" value="<?php echo e(old('email', $customer->email)); ?>" required
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input name="phone" value="<?php echo e(old('phone', $customer->phone)); ?>"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
        </div>
        <div class="flex gap-3">
            <button type="submit" class="flex-1 bg-brand-red text-white py-2.5 rounded-lg font-medium hover:bg-red-dark transition">Save Changes</button>
            <a href="<?php echo e(route('admin.customers.index')); ?>" class="flex-1 text-center border border-gray-200 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\customers\edit.blade.php ENDPATH**/ ?>