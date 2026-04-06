

<?php $__env->startSection('title', 'AI Predictions'); ?>

<?php $__env->startSection('content'); ?>
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="bi bi-graph-up text-brand-red"></i> AI Predictions
            </h1>
            <p class="text-sm text-gray-500 mt-1">Forecasts and analytics computed from real business data.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('admin.ai.overview')); ?>?period=<?php echo e($period ?? 'last_30_days'); ?>"
               class="flex items-center gap-2 border border-gray-200 text-gray-600 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors">
                <i class="bi bi-stars"></i> Overview
            </a>
            <form method="GET" class="flex items-center gap-2">
                <select name="period" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red bg-white" onchange="this.form.submit()">
                    <?php
                        $options = [
                            'last_7_days'  => 'Last 7 Days',
                            'last_30_days' => 'Last 30 Days',
                            'this_month'   => 'This Month',
                            'last_month'   => 'Last Month',
                        ];
                    ?>
                    <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($key); ?>" <?php if(($period ?? 'last_30_days') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </form>
        </div>
    </div>

    <?php if(isset($error)): ?>
        <div class="bg-amber-50 border border-amber-200 text-amber-700 rounded-xl p-4 mb-6 flex items-center gap-3">
            <i class="bi bi-exclamation-triangle-fill text-lg shrink-0"></i>
            <div>
                <p class="font-semibold">AI service unavailable</p>
                <p class="text-sm mt-0.5"><?php echo e($error); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if(!$overview): ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center text-gray-400">
            <i class="bi bi-graph-up text-5xl opacity-20 block mb-3"></i>
            <p class="font-semibold text-gray-500">Predictions not available</p>
            <p class="text-sm mt-1">Ensure the AI service is running on port 8001.</p>
        </div>
    <?php else: ?>
        <?php
            $cards = $overview['cards'] ?? [];
            $topProducts = $overview['top_products'] ?? null;
            $stockAlerts = $overview['stock_alerts'] ?? null;
            $categoryTrends = $overview['category_trends'] ?? null;
            $customerInsights = $overview['customer_insights'] ?? null;
            $salesForecast = $overview['sales_forecast'] ?? null;
        ?>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-<?php echo e(max(count($cards), 1) > 3 ? '4' : '3'); ?> gap-4 mb-6">
            <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $icon  = $card['icon'] ?? 'bi-stars';
                    $dir   = $card['change_direction'] ?? 'flat';
                    $arrow = $dir === 'up' ? 'bi-arrow-up-short' : ($dir === 'down' ? 'bi-arrow-down-short' : 'bi-dash');
                    $changeCls = $dir === 'up'   ? 'bg-green-100 text-green-700'
                               : ($dir === 'down' ? 'bg-red-100 text-red-700'
                               : 'bg-gray-100 text-gray-600');
                ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gray-50 group-hover:bg-red-50 flex items-center justify-center transition-colors">
                            <i class="bi <?php echo e($icon); ?> text-brand-red text-lg"></i>
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
            
            <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <i class="bi bi-calendar2-range text-brand-red"></i>
                    <h2 class="font-semibold text-gray-900">Sales Forecast</h2>
                </div>

                <?php if(!$salesForecast): ?>
                    <p class="text-sm text-gray-500">No forecast data.</p>
                <?php else: ?>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Model</span>
                            <span class="font-medium text-gray-700"><?php echo e($salesForecast['model'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Forecast days</span>
                            <span class="font-medium text-gray-700"><?php echo e($salesForecast['forecast_days'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total predicted</span>
                            <span class="font-medium text-gray-900">Rs. <?php echo e(number_format($salesForecast['total_predicted_revenue'] ?? 0, 0)); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Avg / day</span>
                            <span class="font-medium text-gray-700">Rs. <?php echo e(number_format($salesForecast['avg_daily_revenue'] ?? 0, 0)); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">History used</span>
                            <span class="font-medium text-gray-700"><?php echo e($salesForecast['data_points_used'] ?? 'N/A'); ?> days</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-trophy text-brand-red"></i>
                        <h2 class="font-semibold text-gray-900">Top Products</h2>
                    </div>
                </div>

                <?php if(empty($topProducts)): ?>
                    <p class="text-sm text-gray-500">No top product data.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="text-left px-3 py-2 text-gray-600 font-semibold">Rank</th>
                                    <th class="text-left px-3 py-2 text-gray-600 font-semibold">Product</th>
                                    <th class="text-right px-3 py-2 text-gray-600 font-semibold">Qty</th>
                                    <th class="text-right px-3 py-2 text-gray-600 font-semibold">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-gray-700 font-medium"><?php echo e($row['rank'] ?? ''); ?></td>
                                        <td class="px-3 py-2 text-gray-900 font-medium"><?php echo e($row['name'] ?? ''); ?></td>
                                        <td class="px-3 py-2 text-right text-gray-700"><?php echo e($row['quantity_sold'] ?? 0); ?></td>
                                        <td class="px-3 py-2 text-right text-gray-900 font-medium">Rs. <?php echo e(number_format($row['revenue'] ?? 0, 0)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-exclamation-triangle text-brand-red"></i>
                        <h2 class="font-semibold text-gray-900">Stock Alerts</h2>
                    </div>
                </div>

                <?php if(empty($stockAlerts)): ?>
                    <p class="text-sm text-gray-500">No stock risk data.</p>
                <?php else: ?>
                    <p class="text-xs text-gray-500 mb-3">
                        Note: inventory/stock levels are not available in the current analytics schema, so urgency is based on demand velocity (sold in last 30 days).
                    </p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="text-left px-3 py-2 text-gray-600 font-semibold">Product</th>
                                    <th class="text-right px-3 py-2 text-gray-600 font-semibold">Stock</th>
                                    <th class="text-right px-3 py-2 text-gray-600 font-semibold">Days Left</th>
                                    <th class="text-left px-3 py-2 text-gray-600 font-semibold">Urgency</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php $__currentLoopData = $stockAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $urgency = $row['urgency'] ?? 'ok';
                                        $class = $urgency === 'critical' ? 'bg-red-100 text-red-700' : ($urgency === 'warning' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600');
                                    ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-gray-900 font-medium"><?php echo e($row['name'] ?? ''); ?></td>
                                        <td class="px-3 py-2 text-right text-gray-700">
                                            <?php echo e(array_key_exists('current_stock', $row) && $row['current_stock'] !== null ? number_format((float) $row['current_stock'], 0) : 'N/A'); ?>

                                        </td>
                                        <td class="px-3 py-2 text-right text-gray-900 font-medium">
                                            <?php echo e(array_key_exists('days_until_stockout', $row) && $row['days_until_stockout'] !== null ? number_format((float) $row['days_until_stockout'], 0) : 'N/A'); ?>

                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($class); ?>"><?php echo e(ucfirst($urgency)); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="bi bi-graph-up-arrow text-brand-red"></i>
                        <h2 class="font-semibold text-gray-900">Category Trends</h2>
                    </div>

                    <?php if(empty($categoryTrends)): ?>
                        <p class="text-sm text-gray-500">No category trends data.</p>
                    <?php else: ?>
                        <div class="space-y-2 text-sm">
                            <?php $__currentLoopData = array_slice($categoryTrends, 0, 8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $dir = $row['direction'] ?? 'flat';
                                    $class = $dir === 'up' ? 'bg-green-100 text-green-700' : ($dir === 'down' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600');
                                ?>
                                <div class="flex items-center justify-between gap-3 bg-gray-50 rounded-lg px-3 py-2">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 truncate"><?php echo e($row['name'] ?? ''); ?></p>
                                        <p class="text-xs text-gray-500">Qty: <?php echo e($row['quantity'] ?? 0); ?> | Revenue: Rs. <?php echo e(number_format($row['revenue'] ?? 0, 0)); ?></p>
                                    </div>
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($class); ?>">
                                        <?php echo e(number_format($row['change_percent'] ?? 0, 1)); ?>%
                                    </span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="bi bi-people text-brand-red"></i>
                        <h2 class="font-semibold text-gray-900">Repeat Customers</h2>
                    </div>

                    <?php if(empty($customerInsights)): ?>
                        <p class="text-sm text-gray-500">No customer analytics data.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-gray-500 text-xs">Champions</p>
                                <p class="text-gray-900 font-bold"><?php echo e($customerInsights['champions'] ?? 0); ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-gray-500 text-xs">Loyal</p>
                                <p class="text-gray-900 font-bold"><?php echo e($customerInsights['loyal'] ?? 0); ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-gray-500 text-xs">At risk</p>
                                <p class="text-gray-900 font-bold"><?php echo e($customerInsights['at_risk'] ?? 0); ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-gray-500 text-xs">Likely repeat</p>
                                <p class="text-gray-900 font-bold"><?php echo e($customerInsights['likely_repeat'] ?? 0); ?></p>
                            </div>
                        </div>
                        <?php if(!empty($customerInsights['total_customers'])): ?>
                            <p class="text-xs text-gray-500 mt-3">Total customers scored: <?php echo e($customerInsights['total_customers']); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php $recs = $overview['recommendations'] ?? []; ?>
        <?php if(!empty($recs)): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mt-6">
                <div class="flex items-center gap-2 mb-3">
                    <i class="bi bi-lightbulb text-brand-red"></i>
                    <h2 class="font-semibold text-gray-900">AI Recommendations</h2>
                </div>
                <ul class="space-y-2">
                    <?php $__currentLoopData = $recs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="text-sm text-gray-700 flex items-start gap-2">
                            <i class="bi bi-check-circle-fill text-green-600 mt-0.5"></i>
                            <span><?php echo e($rec); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\ai\predictions.blade.php ENDPATH**/ ?>