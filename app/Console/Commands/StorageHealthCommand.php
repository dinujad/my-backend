<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\ProductMediaPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StorageHealthCommand extends Command
{
    protected $signature = 'storage:health';

    protected $description = 'Check storage symlink and list uploaded files missing from disk';

    public function handle(): int
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (is_link($link)) {
            $this->info('Symlink OK: public/storage → '.readlink($link));
        } elseif (is_dir($link)) {
            $this->warn('public/storage is a directory, not a symlink. Run: php artisan storage:link --force');
        } else {
            $this->error('Missing public/storage. Run: php artisan storage:link --force');
        }

        $this->line('Upload disk: '.$target);
        $this->line('APP_URL: '.config('app.url'));
        $this->newLine();

        $missingMedia = 0;
        Media::query()->orderBy('id')->chunk(100, function ($rows) use (&$missingMedia) {
            foreach ($rows as $media) {
                if (! $media->existsOnDisk()) {
                    $missingMedia++;
                    $this->line("  [media #{$media->id}] missing: {$media->path} ({$media->filename})");
                }
            }
        });

        $missingProducts = 0;
        Product::query()->whereNotNull('image')->where('image', '!=', '')->chunkById(100, function ($products) use (&$missingProducts) {
            foreach ($products as $product) {
                if (! ProductMediaPath::existsOnDisk($product->image)) {
                    $missingProducts++;
                    $this->line("  [product #{$product->id}] missing image: {$product->image}");
                }
            }
        });

        $missingGallery = 0;
        ProductImage::query()->whereNotNull('file_path')->where('file_path', '!=', '')->chunkById(100, function ($images) use (&$missingGallery) {
            foreach ($images as $image) {
                if (! ProductMediaPath::existsOnDisk($image->file_path)) {
                    $missingGallery++;
                    $this->line("  [gallery #{$image->id}] missing: {$image->file_path}");
                }
            }
        });

        $uploadDirs = ['uploads', 'products', 'hero-slides', 'promo-banners', 'orders'];
        $fileCount = 0;
        foreach ($uploadDirs as $dir) {
            if (Storage::disk('public')->exists($dir)) {
                $files = Storage::disk('public')->allFiles($dir);
                $fileCount += count($files);
            }
        }

        $this->newLine();
        $this->info("Files on disk (upload folders): {$fileCount}");
        $this->info("Missing media library rows: {$missingMedia}");
        $this->info("Missing product images: {$missingProducts}");
        $this->info("Missing gallery images: {$missingGallery}");

        if ($missingMedia + $missingProducts + $missingGallery > 0 && $fileCount === 0) {
            $this->newLine();
            $this->warn('Database has image records but the upload folder is empty.');
            $this->warn('This usually means the server was redeployed without a persistent volume.');
            $this->warn('Fix: mount Coolify volume → /var/www/html/storage/app/public');
            $this->warn('Then re-upload images (old files cannot be recovered from DB alone).');
        }

        return self::SUCCESS;
    }
}
