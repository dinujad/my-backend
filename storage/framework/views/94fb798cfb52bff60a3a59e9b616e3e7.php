

<?php $__env->startSection('title', 'Payment Methods'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-8">

  
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="bi bi-credit-card text-brand-red"></i> Payment Methods
      </h1>
      <p class="text-sm text-gray-500 mt-1">Manage payment methods and PayHere gateway settings.</p>
    </div>
    <a href="<?php echo e(route('admin.payments.create')); ?>"
       class="inline-flex items-center gap-2 rounded-xl bg-brand-red px-4 py-2.5 text-sm font-bold text-white shadow hover:bg-red-700 transition">
      <i class="bi bi-plus-lg"></i> Add Payment Method
    </a>
  </div>

  
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 bg-gray-50 flex items-center gap-2">
      <i class="bi bi-list-ul text-brand-red"></i>
      <h2 class="font-bold text-gray-800">Configured Payment Methods</h2>
    </div>

    <?php if($methods->isEmpty()): ?>
    <div class="p-12 text-center">
      <i class="bi bi-credit-card text-4xl text-gray-300 block mb-3"></i>
      <p class="text-gray-400 font-semibold">No payment methods yet.</p>
      <a href="<?php echo e(route('admin.payments.create')); ?>"
         class="mt-4 inline-flex items-center gap-2 rounded-xl bg-brand-red px-5 py-2.5 text-sm font-bold text-white">
        <i class="bi bi-plus-lg"></i> Add First Method
      </a>
    </div>
    <?php else: ?>
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-gray-100 bg-gray-50/80">
          <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide w-8">#</th>
          <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Method</th>
          <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
          <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
          <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php $__currentLoopData = $methods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="hover:bg-gray-50/60 transition">
          <td class="px-5 py-3 text-gray-400 font-mono text-xs"><?php echo e($method->sort_order); ?></td>
          <td class="px-5 py-3">
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-red/10 text-brand-red">
                <?php if($method->code === 'cod'): ?>
                  <i class="bi bi-cash-coin text-lg"></i>
                <?php elseif($method->code === 'payhere'): ?>
                  <i class="bi bi-credit-card-2-front text-lg"></i>
                <?php else: ?>
                  <i class="bi bi-wallet2 text-lg"></i>
                <?php endif; ?>
              </div>
              <div>
                <p class="font-bold text-gray-900"><?php echo e($method->name); ?></p>
                <?php if($method->description): ?>
                  <p class="text-xs text-gray-400"><?php echo e($method->description); ?></p>
                <?php endif; ?>
                <p class="text-xs text-gray-400 font-mono mt-0.5"><?php echo e($method->code); ?></p>
              </div>
            </div>
          </td>
          <td class="px-5 py-3">
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold <?php echo e($method->type === 'online' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600'); ?>">
              <?php echo e(ucfirst($method->type)); ?>

            </span>
          </td>
          <td class="px-5 py-3">
            <?php if($method->is_active): ?>
              <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">
                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Active
              </span>
            <?php else: ?>
              <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-500">
                <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Inactive
              </span>
            <?php endif; ?>
          </td>
          <td class="px-5 py-3">
            <div class="flex items-center justify-end gap-2">
              
              <form action="<?php echo e(route('admin.payments.toggle', $method)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit"
                        class="inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-xs font-semibold transition
                          <?php echo e($method->is_active ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100'); ?>">
                  <?php echo e($method->is_active ? 'Disable' : 'Enable'); ?>

                </button>
              </form>
              <a href="<?php echo e(route('admin.payments.edit', $method)); ?>"
                 class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                <i class="bi bi-pencil"></i> Edit
              </a>
              <?php if(! in_array($method->code, ['cod', 'payhere'])): ?>
              <form action="<?php echo e(route('admin.payments.destroy', $method)); ?>" method="POST"
                    onsubmit="return confirm('Delete <?php echo e($method->name); ?>?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit"
                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 transition">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <i class="bi bi-shield-lock text-brand-red text-lg"></i>
        <h2 class="font-bold text-gray-800">PayHere Gateway Settings</h2>
      </div>
      <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold
        <?php echo e($settings->mode === 'live' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'); ?>">
        <span class="h-1.5 w-1.5 rounded-full <?php echo e($settings->mode === 'live' ? 'bg-green-500' : 'bg-amber-500'); ?>"></span>
        <?php echo e(strtoupper($settings->mode)); ?> Mode
      </span>
    </div>

    <form action="<?php echo e(route('admin.payments.payhere.update')); ?>" method="POST" class="p-6 space-y-6">
      <?php echo csrf_field(); ?>

      
      <div>
        <label class="block text-sm font-bold text-gray-700 mb-3">Mode</label>
        <div class="flex gap-4">
          <label class="flex items-center gap-3 cursor-pointer rounded-xl border px-5 py-3 transition
            <?php echo e($settings->mode === 'sandbox' ? 'border-amber-400 bg-amber-50' : 'border-gray-200 bg-white hover:bg-gray-50'); ?>">
            <input type="radio" name="mode" value="sandbox" <?php echo e($settings->mode === 'sandbox' ? 'checked' : ''); ?>

                   class="text-brand-red focus:ring-brand-red h-4 w-4">
            <div>
              <p class="font-bold text-gray-800 text-sm">Sandbox</p>
              <p class="text-xs text-gray-400">Testing — no real payments</p>
            </div>
          </label>
          <label class="flex items-center gap-3 cursor-pointer rounded-xl border px-5 py-3 transition
            <?php echo e($settings->mode === 'live' ? 'border-green-400 bg-green-50' : 'border-gray-200 bg-white hover:bg-gray-50'); ?>">
            <input type="radio" name="mode" value="live" <?php echo e($settings->mode === 'live' ? 'checked' : ''); ?>

                   class="text-brand-red focus:ring-brand-red h-4 w-4">
            <div>
              <p class="font-bold text-gray-800 text-sm">Live</p>
              <p class="text-xs text-gray-400">Real payments</p>
            </div>
          </label>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 gap-8">
        
        <div class="space-y-4 p-4 rounded-xl border border-amber-200 bg-amber-50/50">
          <h3 class="font-bold text-amber-800 text-sm flex items-center gap-2">
            <i class="bi bi-flask"></i> Sandbox Credentials
          </h3>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Merchant ID (Sandbox)</label>
            <input type="text" name="merchant_id_sandbox" value="<?php echo e(old('merchant_id_sandbox', $settings->merchant_id_sandbox)); ?>"
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
                   placeholder="e.g. 1211149">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Merchant Secret (Sandbox)</label>
            <input type="password" name="merchant_secret_sandbox"
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
                   placeholder="<?php echo e($settings->merchant_secret_sandbox ? '••••••••• (saved — leave blank to keep)' : 'Enter secret'); ?>">
            <?php if($settings->merchant_secret_sandbox): ?>
              <p class="text-xs text-amber-600 mt-1"><i class="bi bi-lock-fill me-1"></i>Secret is saved (encrypted). Enter new value to replace.</p>
            <?php endif; ?>
          </div>
        </div>

        
        <div class="space-y-4 p-4 rounded-xl border border-green-200 bg-green-50/50">
          <h3 class="font-bold text-green-800 text-sm flex items-center gap-2">
            <i class="bi bi-globe"></i> Live Credentials
          </h3>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Merchant ID (Live)</label>
            <input type="text" name="merchant_id_live" value="<?php echo e(old('merchant_id_live', $settings->merchant_id_live)); ?>"
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
                   placeholder="e.g. 1211150">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Merchant Secret (Live)</label>
            <input type="password" name="merchant_secret_live"
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none focus:border-brand-red focus:ring-2 focus:ring-brand-red/20"
                   placeholder="<?php echo e($settings->merchant_secret_live ? '••••••••• (saved — leave blank to keep)' : 'Enter secret'); ?>">
            <?php if($settings->merchant_secret_live): ?>
              <p class="text-xs text-green-600 mt-1"><i class="bi bi-lock-fill me-1"></i>Secret is saved (encrypted). Enter new value to replace.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      
      <div class="rounded-xl border border-blue-100 bg-blue-50/60 px-4 py-3">
        <p class="text-xs font-semibold text-blue-700 mb-1">Notification URL (configure this in PayHere merchant portal)</p>
        <p class="text-sm font-mono text-blue-900 select-all"><?php echo e(config('app.url')); ?>/api/payments/payhere/notify</p>
      </div>

      <div class="flex justify-end">
        <button type="submit"
                class="rounded-xl bg-brand-red px-6 py-2.5 text-sm font-bold text-white shadow hover:bg-red-700 transition">
          <i class="bi bi-save me-1"></i> Save PayHere Settings
        </button>
      </div>
    </form>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\payments\index.blade.php ENDPATH**/ ?>