

<?php $__env->startSection('title', $method ? 'Edit Method' : 'Create Method'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 max-w-4xl mx-auto" x-data="{ isFree: <?php echo e($method?->is_free ? 'true' : 'false'); ?>, selectedZone: '<?php echo e($method?->shipping_zone_id ?? ''); ?>' }">

  <div class="mb-6">
    <a href="<?php echo e(route('admin.shipping.index')); ?>" class="text-sm text-gray-500 hover:text-brand-red flex items-center gap-1">
      <i class="bi bi-arrow-left"></i> Back to Shipping
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2 flex items-center gap-2">
      <i class="bi bi-truck text-brand-red"></i>
      <?php echo e($method ? 'Edit Method: ' . $method->name : 'Create Shipping Method'); ?>

    </h1>
  </div>

  <form method="POST"
        action="<?php echo e($method ? route('admin.shipping.methods.update', $method) : route('admin.shipping.methods.store')); ?>"
        class="space-y-6">
    <?php echo csrf_field(); ?>
    <?php if($method): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

    <?php if($errors->any()): ?>
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
      <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <li><?php echo e($error); ?></li> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
    <?php endif; ?>

    
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">
      <h2 class="font-bold text-gray-800 text-base">Method Details</h2>

      <div class="grid sm:grid-cols-2 gap-4">
        
        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Shipping Zone <span class="text-brand-red">*</span></label>
          <select name="shipping_zone_id" required
                  class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20 bg-white">
            <option value="">Select zone…</option>
            <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($zone->id); ?>" <?php echo e(old('shipping_zone_id', $method?->shipping_zone_id) == $zone->id ? 'selected' : ''); ?>>
              <?php echo e($zone->name); ?>

            </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Method Name <span class="text-brand-red">*</span></label>
          <input type="text" name="name" value="<?php echo e(old('name', $method?->name)); ?>" required
                 class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
                 placeholder="e.g. Standard Delivery">
        </div>

        
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Estimated Delivery</label>
          <input type="text" name="estimated_days" value="<?php echo e(old('estimated_days', $method?->estimated_days)); ?>"
                 class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
                 placeholder="e.g. 2-3 business days">
        </div>

        
        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
          <input type="text" name="description" value="<?php echo e(old('description', $method?->description)); ?>"
                 class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
                 placeholder="Short description shown to customers">
        </div>

        
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Base Price (Rs.) <span class="text-brand-red">*</span></label>
          <input type="number" name="base_price" step="0.01" min="0"
                 value="<?php echo e(old('base_price', $method?->base_price ?? 0)); ?>"
                 :disabled="isFree"
                 class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20 disabled:bg-gray-100 disabled:text-gray-400">
          <p class="text-xs text-gray-400 mt-1">Default price when no district override is set.</p>
        </div>

        
        <div x-show="!isFree">
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Free if Order ≥ (Rs.)</label>
          <input type="number" name="free_shipping_threshold" step="0.01" min="0"
                 value="<?php echo e(old('free_shipping_threshold', $method?->free_shipping_threshold)); ?>"
                 class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
                 placeholder="Leave blank to disable">
        </div>

        
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Sort Order</label>
          <input type="number" name="sort_order" min="0"
                 value="<?php echo e(old('sort_order', $method?->sort_order ?? 0)); ?>"
                 class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20">
        </div>

        <div class="flex flex-col gap-3 justify-end pb-1">
          <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="is_active" value="1"
                   <?php echo e(old('is_active', $method?->is_active ?? true) ? 'checked' : ''); ?>

                   class="h-5 w-5 rounded border-gray-300 text-brand-red focus:ring-brand-red">
            <span class="text-sm font-semibold text-gray-700">Active</span>
          </label>
          <label class="flex items-center gap-3 cursor-pointer" @click="isFree = !isFree">
            <input type="checkbox" name="is_free" value="1"
                   <?php echo e(old('is_free', $method?->is_free) ? 'checked' : ''); ?>

                   x-model="isFree"
                   class="h-5 w-5 rounded border-gray-300 text-brand-red focus:ring-brand-red">
            <span class="text-sm font-semibold text-gray-700">Always Free Shipping</span>
          </label>
        </div>
      </div>
    </div>

    
    <?php if($method && isset($districts) && $districts->count()): ?>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
      <h2 class="font-bold text-gray-800 text-base mb-1">Per-District Price Overrides</h2>
      <p class="text-sm text-gray-500 mb-5">Leave price blank to use the base price above. Check "Free" to make shipping free for that district.</p>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">District</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Price (Rs.)</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Free</th>
              <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Free if Order ≥ (Rs.)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php $__currentLoopData = $districts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $district): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $rate = $rates[$district->id] ?? null; ?>
            <tr class="hover:bg-gray-50/60">
              <td class="px-4 py-2.5 font-semibold text-gray-800"><?php echo e($district->name); ?></td>
              <td class="px-4 py-2.5">
                <input type="number" name="rates[<?php echo e($district->id); ?>]" step="0.01" min="0"
                       value="<?php echo e(old("rates.{$district->id}", $rate?->price)); ?>"
                       class="w-28 rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-900 outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red/20"
                       placeholder="Base">
              </td>
              <td class="px-4 py-2.5">
                <input type="checkbox" name="rates_free[<?php echo e($district->id); ?>]" value="1"
                       <?php echo e($rate?->is_free ? 'checked' : ''); ?>

                       class="h-4 w-4 rounded border-gray-300 text-brand-red focus:ring-brand-red">
              </td>
              <td class="px-4 py-2.5">
                <input type="number" name="rates_threshold[<?php echo e($district->id); ?>]" step="0.01" min="0"
                       value="<?php echo e(old("rates_threshold.{$district->id}", $rate?->free_shipping_threshold)); ?>"
                       class="w-28 rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-900 outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red/20"
                       placeholder="None">
              </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    
    <div class="flex justify-end gap-3 pt-2">
      <a href="<?php echo e(route('admin.shipping.index')); ?>"
         class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
        Cancel
      </a>
      <button type="submit"
              class="rounded-xl bg-brand-red px-6 py-2.5 text-sm font-bold text-white shadow hover:bg-red-700 transition">
        <?php echo e($method ? 'Update Method' : 'Create Method'); ?>

      </button>
    </div>
  </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\shipping\method-form.blade.php ENDPATH**/ ?>