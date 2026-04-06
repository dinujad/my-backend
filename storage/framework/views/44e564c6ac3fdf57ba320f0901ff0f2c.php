

<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
    <p class="text-sm text-gray-500 mt-1">Welcome back, <?php echo e(auth()->user()->name); ?></p>
</div>


<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Revenue</p>
            <span class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="bi bi-currency-dollar text-green-600 text-lg"></i>
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900">Rs. <?php echo e(number_format($stats['revenue_total'], 0)); ?></p>
        <p class="text-xs text-gray-400 mt-1">Paid orders</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Orders</p>
            <span class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="bi bi-cart3 text-blue-600 text-lg"></i>
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['orders_count']); ?></p>
        <p class="text-xs text-orange-500 mt-1"><i class="bi bi-clock me-1"></i><?php echo e($stats['orders_pending']); ?> pending</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Customers</p>
            <span class="w-9 h-9 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="bi bi-people text-purple-600 text-lg"></i>
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['customers_count']); ?></p>
        <p class="text-xs text-gray-400 mt-1">Registered</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Products</p>
            <span class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="bi bi-box-seam text-red-600 text-lg"></i>
            </span>
        </div>
        <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['products_count']); ?></p>
        <p class="text-xs text-gray-400 mt-1"><?php echo e($stats['categories_count']); ?> categories</p>
    </div>
</div>


<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
    <a href="<?php echo e(route('admin.products.create')); ?>" class="flex items-center gap-3 bg-white border border-gray-100 rounded-xl px-4 py-3 hover:border-brand-red hover:shadow-sm transition">
        <i class="bi bi-plus-circle text-brand-red text-xl"></i>
        <span class="text-sm font-medium text-gray-700">Add Product</span>
    </a>
    <a href="<?php echo e(route('admin.orders.create')); ?>" class="flex items-center gap-3 bg-white border border-gray-100 rounded-xl px-4 py-3 hover:border-brand-red hover:shadow-sm transition">
        <i class="bi bi-cart-plus text-brand-red text-xl"></i>
        <span class="text-sm font-medium text-gray-700">New Order</span>
    </a>
    <a href="<?php echo e(route('admin.blog.create')); ?>" class="flex items-center gap-3 bg-white border border-gray-100 rounded-xl px-4 py-3 hover:border-brand-red hover:shadow-sm transition">
        <i class="bi bi-pencil-square text-brand-red text-xl"></i>
        <span class="text-sm font-medium text-gray-700">New Blog Post</span>
    </a>
    <a href="<?php echo e(route('admin.reports.index')); ?>" class="flex items-center gap-3 bg-white border border-gray-100 rounded-xl px-4 py-3 hover:border-brand-red hover:shadow-sm transition">
        <i class="bi bi-graph-up text-brand-red text-xl"></i>
        <span class="text-sm font-medium text-gray-700">View Reports</span>
    </a>
</div>


<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                <i class="bi bi-cart-check text-brand-red"></i> Recent Orders
            </h2>
            <a href="<?php echo e(route('admin.orders.index')); ?>" class="text-xs text-brand-red hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-50">
            <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($order->order_number); ?></p>
                        <p class="text-xs text-gray-500"><?php echo e($order->customer?->name ?? 'Guest'); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">Rs. <?php echo e(number_format($order->total, 0)); ?></p>
                        <?php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'processing' => 'bg-blue-100 text-blue-700',
                                'shipped' => 'bg-purple-100 text-purple-700',
                                'delivered' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ];
                        ?>
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-medium <?php echo e($statusColors[$order->status] ?? 'bg-gray-100 text-gray-600'); ?>">
                            <?php echo e(ucfirst($order->status)); ?>

                        </span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No orders yet.</div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                <i class="bi bi-box-seam text-brand-red"></i> Recent Products
            </h2>
            <a href="<?php echo e(route('admin.products.index')); ?>" class="text-xs text-brand-red hover:underline">View all</a>
        </div>
        <div class="divide-y divide-gray-50">
            <?php $__empty_1 = true; $__currentLoopData = $recentProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($product->name); ?></p>
                        <p class="text-xs text-gray-500"><?php echo e($product->category?->name ?? '–'); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">Rs. <?php echo e(number_format($product->price, 0)); ?></p>
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-medium <?php echo e($product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                            <?php echo e($product->is_active ? 'Active' : 'Inactive'); ?>

                        </span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No products yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>