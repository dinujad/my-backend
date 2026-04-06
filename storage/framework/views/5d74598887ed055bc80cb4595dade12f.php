

<?php $__env->startSection('title', 'AI Overview'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i class="bi bi-stars text-brand-red"></i> AI Overview
        </h1>
        <p class="text-sm text-gray-500 mt-1">Sales, stock, customers, and category insights — grounded in real data.</p>
    </div>

    <form method="GET" class="flex items-center gap-2">
        <label class="text-sm text-gray-500 font-medium">Period:</label>
        <select
            name="period"
            class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white"
            onchange="this.form.submit()"
        >
            <?php
                $options = [
                    'today'       => 'Today',
                    'yesterday'   => 'Yesterday',
                    'this_week'   => 'This Week',
                    'last_week'   => 'Last Week',
                    'last_7_days' => 'Last 7 Days',
                    'last_30_days'=> 'Last 30 Days',
                    'this_month'  => 'This Month',
                    'last_month'  => 'Last Month',
                    'this_year'   => 'This Year',
                ];
            ?>
            <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($key); ?>" <?php if(($period ?? 'last_30_days') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <a href="<?php echo e(route('admin.ai.predictions')); ?>?period=<?php echo e($period ?? 'last_30_days'); ?>"
           class="flex items-center gap-2 bg-brand-red text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-red-700 transition-colors shadow-sm shadow-brand-red/20">
            <i class="bi bi-graph-up-arrow"></i> Predictions
        </a>
    </form>
</div>


<?php if(isset($error)): ?>
    <div class="bg-amber-50 border border-amber-200 text-amber-700 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill text-lg shrink-0"></i>
        <div>
            <p class="font-semibold">AI service unavailable</p>
            <p class="text-sm mt-0.5"><?php echo e($error); ?> Make sure the Python AI service is running on port 8001.</p>
        </div>
    </div>
<?php endif; ?>

<?php if(!$overview): ?>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <?php for($i = 0; $i < 3; $i++): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 animate-pulse">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-100"></div>
                    <div class="h-4 bg-gray-100 rounded w-28"></div>
                </div>
                <div class="h-7 bg-gray-100 rounded w-20 mb-2"></div>
                <div class="h-3 bg-gray-100 rounded w-36"></div>
            </div>
        <?php endfor; ?>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center text-gray-400">
        <i class="bi bi-stars text-4xl opacity-30 block mb-2"></i>
        <p class="font-semibold">AI overview not available</p>
        <p class="text-sm mt-1">Start the AI service and try again.</p>
    </div>
<?php else: ?>
    <?php
        $cards           = $overview['cards'] ?? [];
        $recommendations = $overview['recommendations'] ?? [];
        $topProducts     = $overview['top_products'] ?? [];
        $stockAlerts     = $overview['stock_alerts'] ?? [];
    ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-<?php echo e(max(count($cards), 1) > 3 ? '4' : '3'); ?> gap-4 mb-6">
        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $dir   = $card['change_direction'] ?? 'flat';
                $arrow = $dir === 'up' ? 'bi-arrow-up-short' : ($dir === 'down' ? 'bi-arrow-down-short' : 'bi-dash');
                $changeCls = $dir === 'up'   ? 'bg-green-100 text-green-700'
                           : ($dir === 'down' ? 'bg-red-100 text-red-700'
                           : 'bg-gray-100 text-gray-600');
                $iconName = $card['icon'] ?? 'bi-stars';
            ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl bg-gray-50 group-hover:bg-red-50 flex items-center justify-center transition-colors">
                        <i class="bi <?php echo e($iconName); ?> text-brand-red text-lg"></i>
                    </div>
                    <?php if(!empty($card['change'])): ?>
                        <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-bold <?php echo e($changeCls); ?>">
                            <i class="bi <?php echo e($arrow); ?> text-sm"></i><?php echo e($card['change']); ?>

                        </span>
                    <?php endif; ?>
                </div>
                <p class="text-2xl font-black text-gray-900 mb-1"><?php echo e($card['value'] ?? '—'); ?></p>
                <p class="text-sm font-semibold text-gray-600"><?php echo e($card['title'] ?? ''); ?></p>
                <?php if(!empty($card['detail'])): ?>
                    <p class="text-xs text-gray-400 mt-1"><?php echo e($card['detail']); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 flex items-center gap-2">
                    <i class="bi bi-lightbulb-fill text-brand-red"></i> AI Recommendations
                </h2>
                <span class="text-xs text-gray-400 font-medium"><?php echo e(ucwords(str_replace('_', ' ', $period ?? 'last_30_days'))); ?></span>
            </div>
            <?php if(empty($recommendations)): ?>
                <p class="text-sm text-gray-400 text-center py-6">No recommendations for this period.</p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-start gap-3 p-3 rounded-xl <?php echo e($idx % 2 === 0 ? 'bg-gray-50' : 'bg-white'); ?> border border-gray-50">
                            <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="bi bi-check-lg text-green-600 text-xs font-bold"></i>
                            </div>
                            <span class="text-sm text-gray-700"><?php echo e($rec); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </div>

        
        <div class="space-y-4">
            
            <?php if(!empty($topProducts)): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2 mb-3">
                        <i class="bi bi-trophy-fill text-brand-red text-sm"></i> Top Products
                    </h3>
                    <div class="space-y-2">
                        <?php $__currentLoopData = array_slice($topProducts, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-[11px] font-black text-gray-400 w-4 shrink-0"><?php echo e($idx + 1); ?></span>
                                    <span class="text-xs text-gray-700 truncate"><?php echo e($p['name'] ?? $p['product'] ?? '—'); ?></span>
                                </div>
                                <span class="text-xs font-bold text-gray-500 shrink-0"><?php echo e($p['quantity'] ?? $p['total_quantity'] ?? ''); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if(!empty($stockAlerts)): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-amber-500 text-sm"></i> Stock Alerts
                    </h3>
                    <div class="space-y-2">
                        <?php $__currentLoopData = array_slice($stockAlerts, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $lvl = $item['risk_level'] ?? ($item['stock'] < 5 ? 'critical' : 'warning');
                            ?>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs text-gray-700 truncate"><?php echo e($item['name'] ?? $item['product'] ?? '—'); ?></span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded <?php echo e($lvl === 'critical' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'); ?>">
                                    <?php echo e($item['stock'] ?? ''); ?>

                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

            
            <a href="<?php echo e(route('admin.ai.chat')); ?>" class="block bg-brand-red text-white rounded-2xl p-4 hover:bg-red-700 transition-colors group">
                <div class="flex items-center gap-3">
                    <i class="bi bi-chat-left-dots-fill text-2xl text-red-200"></i>
                    <div>
                        <p class="font-bold text-sm">Ask AI Assistant</p>
                        <p class="text-xs text-red-200 mt-0.5">Natural language business queries</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\ai\overview.blade.php ENDPATH**/ ?>