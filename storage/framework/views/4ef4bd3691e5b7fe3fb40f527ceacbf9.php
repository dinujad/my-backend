

<?php $__env->startSection('title', 'Shipping Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-8">

  
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-truck text-brand-red"></i> Shipping Management
      </h1>
      <p class="text-sm text-gray-500 mt-1">Manage zones, methods, and district-based pricing.</p>
    </div>
    <div class="flex gap-3">
      <a href="<?php echo e(route('admin.shipping.zones.create')); ?>"
         class="inline-flex items-center gap-2 rounded-xl bg-brand-red px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-red-700 transition">
        <i class="bi bi-plus-lg"></i> New Zone
      </a>
      <a href="<?php echo e(route('admin.shipping.methods.create')); ?>"
         class="inline-flex items-center gap-2 rounded-xl bg-gray-800 px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-gray-900 transition">
        <i class="bi bi-plus-lg"></i> New Method
      </a>
    </div>
  </div>

  
  <div>
    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
      <i class="bi bi-map text-brand-red"></i> Shipping Zones & Methods
    </h2>

    <?php $__empty_1 = true; $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
      
      <div class="flex items-center justify-between px-5 py-4 bg-gray-50 border-b border-gray-200">
        <div class="flex items-center gap-3">
          <span class="font-bold text-gray-900 text-base"><?php echo e($zone->name); ?></span>
          <?php if($zone->description): ?>
            <span class="text-xs text-gray-500">— <?php echo e($zone->description); ?></span>
          <?php endif; ?>
          <?php if($zone->is_active): ?>
            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Active</span>
          <?php else: ?>
            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500">Inactive</span>
          <?php endif; ?>
        </div>
        <div class="flex items-center gap-2">
          <a href="<?php echo e(route('admin.shipping.zones.edit', $zone)); ?>"
             class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
            <i class="bi bi-pencil"></i> Edit Zone
          </a>
          <form action="<?php echo e(route('admin.shipping.zones.destroy', $zone)); ?>" method="POST"
                onsubmit="return confirm('Delete zone <?php echo e($zone->name); ?>? Methods inside will also be deleted.')">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button type="submit"
                    class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 transition">
              <i class="bi bi-trash"></i>
            </button>
          </form>
        </div>
      </div>

      
      <?php if($zone->districts->count()): ?>
      <div class="px-5 py-3 border-b border-gray-100 bg-blue-50/40">
        <p class="text-xs font-semibold text-gray-500 mb-2">Districts</p>
        <div class="flex flex-wrap gap-1.5">
          <?php $__currentLoopData = $zone->districts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <span class="rounded-full bg-white border border-gray-200 px-2.5 py-0.5 text-xs font-medium text-gray-700">
            <?php echo e($d->name); ?>

          </span>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
      <?php endif; ?>

      
      <?php if($zone->methods->count()): ?>
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/80">
            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Method</th>
            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Base Price</th>
            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Delivery</th>
            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
            <th class="px-5 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php $__currentLoopData = $zone->methods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr class="hover:bg-gray-50/60 transition">
            <td class="px-5 py-3">
              <p class="font-semibold text-gray-900"><?php echo e($method->name); ?></p>
              <?php if($method->description): ?>
                <p class="text-xs text-gray-400 mt-0.5"><?php echo e($method->description); ?></p>
              <?php endif; ?>
              <?php if($method->is_free): ?>
                <span class="inline-flex mt-1 items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Free</span>
              <?php endif; ?>
            </td>
            <td class="px-5 py-3">
              <?php if($method->is_free): ?>
                <span class="font-bold text-emerald-600">Rs. 0.00</span>
              <?php else: ?>
                <span class="font-bold text-gray-800">Rs. <?php echo e(number_format($method->base_price, 2)); ?></span>
                <?php if($method->free_shipping_threshold): ?>
                  <p class="text-xs text-gray-400">Free if order ≥ Rs. <?php echo e(number_format($method->free_shipping_threshold, 2)); ?></p>
                <?php endif; ?>
              <?php endif; ?>
            </td>
            <td class="px-5 py-3 text-gray-600"><?php echo e($method->estimated_days ?? '—'); ?></td>
            <td class="px-5 py-3">
              <?php if($method->is_active): ?>
                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Active</span>
              <?php else: ?>
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500">Inactive</span>
              <?php endif; ?>
            </td>
            <td class="px-5 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                <a href="<?php echo e(route('admin.shipping.methods.edit', $method)); ?>"
                   class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                  <i class="bi bi-pencil"></i> Edit
                </a>
                <form action="<?php echo e(route('admin.shipping.methods.destroy', $method)); ?>" method="POST"
                      onsubmit="return confirm('Delete method <?php echo e($method->name); ?>?')">
                  <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                  <button type="submit"
                          class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 transition">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="px-5 py-6 text-center text-sm text-gray-400">
        No shipping methods yet.
        <a href="<?php echo e(route('admin.shipping.methods.create')); ?>?zone=<?php echo e($zone->id); ?>" class="text-brand-red font-semibold hover:underline ml-1">Add one →</a>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center">
      <i class="bi bi-truck text-4xl text-gray-300"></i>
      <p class="mt-3 text-gray-500 font-semibold">No shipping zones configured yet.</p>
      <a href="<?php echo e(route('admin.shipping.zones.create')); ?>"
         class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-red px-5 py-2.5 text-sm font-bold text-white shadow hover:bg-red-700 transition">
        <i class="bi bi-plus-lg"></i> Create First Zone
      </a>
    </div>
    <?php endif; ?>
  </div>

  
  <div>
    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
      <i class="bi bi-geo-alt text-brand-red"></i> Districts (25 Sri Lanka Districts)
    </h2>
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-200">
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">District</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Province</th>
            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Zone</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php $__currentLoopData = $districts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $district): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr class="hover:bg-gray-50/60">
            <td class="px-5 py-2.5 font-semibold text-gray-900"><?php echo e($district->name); ?></td>
            <td class="px-5 py-2.5 text-gray-500"><?php echo e($district->province); ?></td>
            <td class="px-5 py-2.5">
              <?php if($district->zone): ?>
                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                  <?php echo e($district->zone->name); ?>

                </span>
              <?php else: ?>
                <span class="text-gray-400 text-xs">No zone</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\shipping\index.blade.php ENDPATH**/ ?>