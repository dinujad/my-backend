<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – Print Works.LK</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { red: '#FF1F40', 'red-dark': '#e01a38' } } } }
        }
    </script>
</head>
<body class="bg-gray-950 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center mb-3">
                <img src="<?php echo e(asset('logo.png')); ?>" alt="Print Works LK" class="h-12 object-contain">
            </div>
            <p class="text-gray-400 text-sm mt-1">Admin Panel</p>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl">
            <?php if($errors->any()): ?>
                <div class="mb-4 flex items-center gap-2 bg-red-900/50 border border-red-700 text-red-300 rounded-lg px-4 py-3 text-sm">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?php echo e($errors->first()); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Email Address</label>
                    <div class="relative">
                        <i class="bi bi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                        <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:border-brand-red placeholder-gray-600"
                               placeholder="admin@printworks.lk">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                    <div class="relative">
                        <i class="bi bi-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                        <input type="password" name="password" required
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:border-brand-red"
                               placeholder="••••••••">
                    </div>
                </div>
                <button type="submit" class="w-full bg-brand-red hover:bg-red-dark text-white font-semibold py-2.5 rounded-lg transition flex items-center justify-center gap-2 mt-2">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>
        </div>
        <p class="text-center text-xs text-gray-600 mt-4">Admin v1.0</p>
    </div>
</body>
</html>
<?php /**PATH C:\dev\printworks\backend\resources\views\admin\login.blade.php ENDPATH**/ ?>