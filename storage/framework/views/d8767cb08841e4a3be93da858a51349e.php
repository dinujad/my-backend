<?php $__env->startSection('title', 'SMS Campaigns'); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex justify-between items-end gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">SMS Campaigns</h1>
        <p class="text-sm text-gray-500">Send bulk SMS messages to your customers using Text.lk.</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-6">
        <form method="POST" action="<?php echo e(route('admin.sms.send')); ?>" enctype="multipart/form-data" class="space-y-6">
            <?php echo csrf_field(); ?>
            
            <?php if($errors->any()): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-lg text-sm border border-red-200 mb-4">
                    <ul class="list-disc list-inside">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Message Body -->
            <div>
                <label for="message" class="block text-sm font-semibold text-gray-800 mb-2">Message Body <span class="text-red-500">*</span></label>
                <textarea name="message" id="message" rows="4" maxlength="160" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Type your SMS here (Max 160 chars)..." required><?php echo e(old('message')); ?></textarea>
                <div class="flex justify-between items-center text-xs text-gray-500 mt-2">
                    <p><i class="bi bi-info-circle"></i> Standard SMS length limit is 160 characters per segment.</p>
                    <span id="char-count">0 / 160</span>
                </div>
            </div>

            <hr class="border-gray-200 my-4">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Manual Numbers -->
                <div>
                    <label for="manual_numbers" class="block text-sm font-semibold text-gray-800 mb-2">Manual Phone Numbers</label>
                    <textarea name="manual_numbers" id="manual_numbers" rows="8" class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="+94712345678&#10;0712345678&#10;94701234567"><?php echo e(old('manual_numbers')); ?></textarea>
                    <p class="text-xs text-gray-500 mt-2">Enter phone numbers separated by new lines, spaces, or commas.</p>
                </div>

                <!-- CSV Upload -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="csv_file" class="block text-sm font-semibold text-gray-800">Upload CSV File</label>
                        <a href="<?php echo e(route('admin.sms.sample')); ?>" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 transition">
                            <i class="bi bi-download"></i> Sample CSV
                        </a>
                    </div>
                    
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center bg-gray-50 hover:bg-gray-100 transition relative">
                        <input type="file" name="csv_file" id="csv_file" accept=".csv" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="flex flex-col items-center justify-center">
                            <i class="bi bi-file-earmark-spreadsheet text-4xl text-gray-400 mb-3"></i>
                            <p class="text-sm text-gray-600 font-medium whitespace-nowrap overflow-hidden text-clip w-full px-2" id="file-name-display">Drop your .csv file here or click to browse</p>
                            <p class="text-xs text-gray-500 mt-1">Maximum file size: 2MB</p>
                        </div>
                    </div>
                    <div class="bg-indigo-50 text-indigo-800 text-xs p-3 rounded-lg mt-3 flex items-start gap-2 border border-indigo-100">
                        <i class="bi bi-lightbulb-fill mt-0.5 text-indigo-500"></i>
                        <p>You can provide numbers manually, via CSV, or <strong>both</strong>! The system automatically removes duplicates before sending.</p>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-md flex items-center gap-2">
                    <i class="bi bi-send-fill"></i> Blast SMS
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('csv_file').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : "Drop your .csv file here or click to browse";
        document.getElementById('file-name-display').textContent = fileName;
        if(e.target.files[0]) {
            document.getElementById('file-name-display').classList.add('text-blue-600', 'font-bold');
        } else {
            document.getElementById('file-name-display').classList.remove('text-blue-600', 'font-bold');
        }
    });

    const msgBox = document.getElementById('message');
    const charCount = document.getElementById('char-count');
    msgBox.addEventListener('input', function() {
        charCount.textContent = this.value.length + " / 160";
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\sms\campaigns.blade.php ENDPATH**/ ?>