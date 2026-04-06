

<?php $__env->startSection('title', $zone ? 'Edit Zone' : 'Create Zone'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 max-w-3xl mx-auto">

  <div class="mb-6">
    <a href="<?php echo e(route('admin.shipping.index')); ?>" class="text-sm text-gray-500 hover:text-brand-red flex items-center gap-1">
      <i class="bi bi-arrow-left"></i> Back to Shipping
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2 flex items-center gap-2">
      <i class="bi bi-map text-brand-red"></i>
      <?php echo e($zone ? 'Edit Zone: ' . $zone->name : 'Create Shipping Zone'); ?>

    </h1>
  </div>

  <form method="POST"
        action="<?php echo e($zone ? route('admin.shipping.zones.update', $zone) : route('admin.shipping.zones.store')); ?>"
        class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <?php echo csrf_field(); ?>
    <?php if($zone): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

    <?php if($errors->any()): ?>
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
      <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
    <?php endif; ?>

    
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">Zone Name <span class="text-brand-red">*</span></label>
      <input type="text" name="name" value="<?php echo e(old('name', $zone?->name)); ?>"
             class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
             placeholder="e.g. Western Province" required>
    </div>

    
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
      <input type="text" name="description" value="<?php echo e(old('description', $zone?->description)); ?>"
             class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
             placeholder="Optional description">
    </div>

    <div class="grid grid-cols-2 gap-4">
      
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Sort Order</label>
        <input type="number" name="sort_order" value="<?php echo e(old('sort_order', $zone?->sort_order ?? 0)); ?>" min="0"
               class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20">
      </div>
      
      <div class="flex items-end pb-1">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $zone?->is_active ?? true) ? 'checked' : ''); ?>

                 class="h-5 w-5 rounded border-gray-300 text-brand-red focus:ring-brand-red">
          <span class="text-sm font-semibold text-gray-700">Active</span>
        </label>
      </div>
    </div>

    
    <?php if($zone && isset($allDistricts)): ?>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-2">Assign Districts to this Zone</label>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 rounded-xl border border-gray-200 bg-gray-50 p-4 max-h-72 overflow-y-auto">
        <?php $__currentLoopData = $allDistricts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $district): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <label class="flex items-center gap-2 cursor-pointer py-1">
          <input type="checkbox" name="district_ids[]" value="<?php echo e($district->id); ?>"
                 <?php echo e(in_array($district->id, $zone->districts->pluck('id')->toArray()) ? 'checked' : ''); ?>

                 class="h-4 w-4 rounded border-gray-300 text-brand-red focus:ring-brand-red">
          <span class="text-sm text-gray-700"><?php echo e($district->name); ?></span>
        </label>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>
    <?php endif; ?>

    
    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
      <a href="<?php echo e(route('admin.shipping.index')); ?>"
         class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
        Cancel
      </a>
      <button type="submit"
              class="rounded-xl bg-brand-red px-6 py-2.5 text-sm font-bold text-white shadow hover:bg-red-700 transition">
        <?php echo e($zone ? 'Update Zone' : 'Create Zone'); ?>

      </button>
    </div>
  </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\shipping\zone-form.blade.php ENDPATH**/ ?>