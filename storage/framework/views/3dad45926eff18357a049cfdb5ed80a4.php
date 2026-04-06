<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
  .wrap { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
  .header { background: #c0392b; color: #fff; padding: 28px 36px; }
  .header h1 { margin: 0; font-size: 20px; }
  .header p { margin: 4px 0 0; opacity: .8; font-size: 13px; }
  .body { padding: 32px 36px; }
  .badge { display: inline-block; background: #fff3f3; color: #c0392b; border: 1px solid #f5c6c6; border-radius: 20px; padding: 4px 14px; font-size: 13px; font-weight: 700; margin-bottom: 20px; }
  h2 { font-size: 16px; color: #333; margin: 0 0 16px; }
  .info-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
  .info-table td { padding: 8px 0; font-size: 14px; border-bottom: 1px solid #f0f0f0; }
  .info-table td:first-child { color: #888; width: 40%; }
  .items-table { width: 100%; border-collapse: collapse; }
  .items-table th { background: #f7f8fa; padding: 9px 12px; font-size: 12px; text-align: left; color: #666; }
  .items-table td { padding: 9px 12px; font-size: 14px; border-bottom: 1px solid #f5f5f5; }
  .btn { display: inline-block; background: #c0392b; color: #fff; padding: 13px 28px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 14px; margin-top: 24px; }
  .footer { padding: 20px 36px; background: #f9f9f9; font-size: 12px; color: #aaa; text-align: center; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>Print Works LK</h1>
    <p>New Quote Request Received</p>
  </div>
  <div class="body">
    <div class="badge">Request #<?php echo e($quoteRequest->request_number); ?></div>
    <h2>Customer Information</h2>
    <table class="info-table">
      <tr><td>Name</td><td><strong><?php echo e($quoteRequest->customer_name); ?></strong></td></tr>
      <?php if($quoteRequest->company_name): ?>
      <tr><td>Company</td><td><?php echo e($quoteRequest->company_name); ?></td></tr>
      <?php endif; ?>
      <tr><td>Email</td><td><?php echo e($quoteRequest->email); ?></td></tr>
      <tr><td>Phone</td><td><?php echo e($quoteRequest->phone); ?></td></tr>
      <tr><td>Contact Preference</td><td><?php echo e(ucfirst($quoteRequest->preferred_contact)); ?></td></tr>
      <tr><td>Urgency</td><td><?php echo e(ucfirst($quoteRequest->urgency ?? 'normal')); ?></td></tr>
      <?php if($quoteRequest->deadline): ?>
      <tr><td>Deadline</td><td><?php echo e($quoteRequest->deadline->format('d M Y')); ?></td></tr>
      <?php endif; ?>
    </table>

    <h2>Requested Items (<?php echo e($quoteRequest->items->count()); ?>)</h2>
    <table class="items-table">
      <thead>
        <tr>
          <th>Product</th>
          <th style="text-align:center;width:70px;">Qty</th>
        </tr>
      </thead>
      <tbody>
        <?php $__currentLoopData = $quoteRequest->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td>
            <strong><?php echo e($item->product_name); ?></strong>
            <?php if($item->product_sku): ?><br><small style="color:#888;">SKU: <?php echo e($item->product_sku); ?></small><?php endif; ?>
            <?php if($item->item_notes): ?><br><small style="color:#666;"><?php echo e($item->item_notes); ?></small><?php endif; ?>
          </td>
          <td style="text-align:center;"><?php echo e($item->quantity); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>

    <?php if($quoteRequest->customer_notes): ?>
    <div style="margin-top:20px;padding:14px;background:#f9f9f9;border-radius:6px;font-size:14px;">
      <strong>Customer Notes:</strong><br><?php echo e($quoteRequest->customer_notes); ?>

    </div>
    <?php endif; ?>

    <a href="<?php echo e(config('app.frontend_url')); ?>/admin/quotes/<?php echo e($quoteRequest->id); ?>" class="btn">
      Review Quote Request →
    </a>
  </div>
  <div class="footer">Print Works LK &bull; printworks.lk</div>
</div>
</body>
</html>
<?php /**PATH C:\dev\printworks\backend\resources\views\emails\new-quote-request.blade.php ENDPATH**/ ?>