

<?php $__env->startSection('title', 'Quote Requests'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-file-earmark-text text-brand-red"></i> Quote Requests
    </h1>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
    <form method="GET" action="<?php echo e(route('admin.quote-requests.index')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input
            type="text"
            name="search"
            value="<?php echo e(request('search')); ?>"
            placeholder="Search request #, name, email, phone..."
            class="md:col-span-2 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-red focus:ring-brand-red"
        >
        <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-red focus:ring-brand-red">
            <option value="">All statuses</option>
            <?php $__currentLoopData = \App\Models\QuoteRequest::$statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($key); ?>" <?php if(request('status') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button type="submit" class="rounded-lg bg-brand-red text-white px-4 py-2 text-sm font-medium hover:bg-red-dark">
            Filter
        </button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Request #</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Customer</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Contact</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Items</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php $__empty_1 = true; $__currentLoopData = $quoteRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $statusColors = [
                        'new' => 'bg-blue-100 text-blue-700',
                        'reviewing' => 'bg-yellow-100 text-yellow-700',
                        'awaiting_pricing' => 'bg-orange-100 text-orange-700',
                        'quoted' => 'bg-indigo-100 text-indigo-700',
                        'sent' => 'bg-violet-100 text-violet-700',
                        'customer_responded' => 'bg-cyan-100 text-cyan-700',
                        'approved' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                        'closed' => 'bg-gray-100 text-gray-700',
                    ];
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-semibold text-gray-900"><?php echo e($qr->request_number); ?></td>
                    <td class="px-4 py-3 text-gray-700">
                        <div class="font-medium"><?php echo e($qr->customer_name); ?></div>
                        <?php if($qr->company_name): ?>
                            <div class="text-xs text-gray-500"><?php echo e($qr->company_name); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        <div><?php echo e($qr->email); ?></div>
                        <div class="text-xs"><?php echo e($qr->phone); ?></div>
                    </td>
                    <td class="px-4 py-3 text-gray-700"><?php echo e($qr->items->count()); ?></td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo e($statusColors[$qr->status] ?? 'bg-gray-100 text-gray-700'); ?>">
                            <?php echo e(\App\Models\QuoteRequest::$statuses[$qr->status] ?? ucfirst($qr->status)); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500"><?php echo e($qr->created_at->format('d M Y')); ?></td>
                    <td class="px-4 py-3 text-right">
                        <a href="<?php echo e(route('admin.quote-requests.show', $qr)); ?>" class="text-brand-red hover:underline text-xs font-medium">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No quote requests found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if($quoteRequests->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($quoteRequests->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\quote-requests\index.blade.php ENDPATH**/ ?>