

<?php $__env->startSection('title', 'Reports'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-graph-up text-brand-red"></i> Reports
    </h1>
    <form method="GET" class="flex items-center gap-2">
        <label class="text-sm text-gray-600">Period:</label>
        <select name="period" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-red">
            <?php $__currentLoopData = ['7' => 'Last 7 days', '30' => 'Last 30 days', '60' => 'Last 60 days', '90' => 'Last 90 days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($val); ?>" <?php echo e($period == $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </form>
</div>


<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-3 mb-2">
            <span class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="bi bi-cash-stack text-green-600"></i>
            </span>
            <p class="text-xs font-semibold text-gray-500 uppercase">Revenue</p>
        </div>
        <p class="text-2xl font-bold text-gray-900">Rs. <?php echo e(number_format($revenueTotal, 0)); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-3 mb-2">
            <span class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="bi bi-cart3 text-blue-600"></i>
            </span>
            <p class="text-xs font-semibold text-gray-500 uppercase">Orders</p>
        </div>
        <p class="text-2xl font-bold text-gray-900"><?php echo e($ordersTotal); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-3 mb-2">
            <span class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="bi bi-person-plus text-purple-600"></i>
            </span>
            <p class="text-xs font-semibold text-gray-500 uppercase">New Customers</p>
        </div>
        <p class="text-2xl font-bold text-gray-900"><?php echo e($newCustomers); ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-3 mb-2">
            <span class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="bi bi-receipt text-orange-600"></i>
            </span>
            <p class="text-xs font-semibold text-gray-500 uppercase">Avg. Order</p>
        </div>
        <p class="text-2xl font-bold text-gray-900">Rs. <?php echo e(number_format($avgOrderValue, 0)); ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="bi bi-pie-chart text-brand-red"></i> Orders by Status
        </h2>
        <?php
            $statusColors = [
                'pending' => 'bg-yellow-100 text-yellow-700',
                'processing' => 'bg-blue-100 text-blue-700',
                'shipped' => 'bg-purple-100 text-purple-700',
                'delivered' => 'bg-green-100 text-green-700',
                'cancelled' => 'bg-red-100 text-red-700',
            ];
            $total = $ordersByStatus->sum();
        ?>
        <?php if($ordersByStatus->isEmpty()): ?>
            <p class="text-gray-400 text-sm">No order data yet.</p>
        <?php else: ?>
            <div class="space-y-3">
                <?php $__currentLoopData = $ordersByStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $pct = $total > 0 ? round($count / $total * 100) : 0; ?>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo e($statusColors[$status] ?? 'bg-gray-100 text-gray-600'); ?>"><?php echo e(ucfirst($status)); ?></span>
                            <span class="text-sm font-semibold text-gray-900"><?php echo e($count); ?> <span class="text-gray-400 font-normal">(<?php echo e($pct); ?>%)</span></span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full bg-brand-red" style="width: <?php echo e($pct); ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="bi bi-trophy text-brand-red"></i> Top Products
            </h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Product</th>
                    <th class="px-4 py-2 text-center font-medium text-gray-600">Qty</th>
                    <th class="px-4 py-2 text-right font-medium text-gray-600">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-900">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 text-xs text-gray-500 mr-1"><?php echo e($i + 1); ?></span>
                            <?php echo e(Str::limit($product->product_name, 30)); ?>

                        </td>
                        <td class="px-4 py-2 text-center text-gray-700"><?php echo e($product->total_qty); ?></td>
                        <td class="px-4 py-2 text-right font-medium text-gray-900">Rs. <?php echo e(number_format($product->total_revenue, 0)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">No sales data yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <?php if($dailyRevenue->count() > 0): ?>
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="bi bi-bar-chart-line text-brand-red"></i> Daily Revenue (Last <?php echo e($period); ?> Days)
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Date</th>
                            <th class="px-4 py-2 text-center font-medium text-gray-600">Orders</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-600">Revenue</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-600">Bar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php $maxRevenue = $dailyRevenue->max('revenue') ?: 1; ?>
                        <?php $__currentLoopData = $dailyRevenue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-700"><?php echo e(\Carbon\Carbon::parse($day->date)->format('D, d M')); ?></td>
                                <td class="px-4 py-2 text-center text-gray-700"><?php echo e($day->orders); ?></td>
                                <td class="px-4 py-2 text-right font-medium text-gray-900">Rs. <?php echo e(number_format($day->revenue, 0)); ?></td>
                                <td class="px-4 py-2 text-right">
                                    <div class="inline-block h-3 bg-brand-red rounded" style="width: <?php echo e(max(4, round($day->revenue / $maxRevenue * 100))); ?>px"></div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\reports\index.blade.php ENDPATH**/ ?>