<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use App\Support\ProductMediaPath;
use Illuminate\Console\Command;

class NormalizeProductImagePaths extends Command
{
    protected $signature = 'products:normalize-image-paths
                            {--dry-run : Show changes without writing to the database}
                            {--report-missing : List rows whose files are missing on disk}';

    protected $description = 'Fix legacy product image paths (localhost URLs, missing storage/ prefix)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $reportMissing = (bool) $this->option('report-missing');

        $updated = 0;
        $missing = 0;

        Product::query()->whereNotNull('image')->where('image', '!=', '')->chunkById(100, function ($products) use ($dryRun, $reportMissing, &$updated, &$missing) {
            foreach ($products as $product) {
                $before = $product->image;
                $after = ProductMediaPath::toDatabaseValue($before);

                if ($after === '' || $after === $before) {
                    if ($reportMissing && $after !== '' && ! ProductMediaPath::existsOnDisk($after)) {
                        $this->warn("Missing file — product #{$product->id} ({$product->slug}): {$after}");
                        $missing++;
                    }
                    continue;
                }

                $this->line("Product #{$product->id}: {$before} → {$after}");
                if (! $dryRun) {
                    $product->update(['image' => $after]);
                }
                $updated++;

                if ($reportMissing && ! ProductMediaPath::existsOnDisk($after)) {
                    $this->warn("  ↳ file still missing on disk: {$after}");
                    $missing++;
                }
            }
        });

        ProductImage::query()->chunkById(100, function ($images) use ($dryRun, $reportMissing, &$updated, &$missing) {
            foreach ($images as $image) {
                $before = $image->file_path;
                $after = ProductMediaPath::toDatabaseValue($before);

                if ($after === '' || $after === $before) {
                    if ($reportMissing && $after !== '' && ! ProductMediaPath::existsOnDisk($after)) {
                        $this->warn("Missing file — product_image #{$image->id}: {$after}");
                        $missing++;
                    }
                    continue;
                }

                $this->line("ProductImage #{$image->id}: {$before} → {$after}");
                if (! $dryRun) {
                    $image->update(['file_path' => $after]);
                }
                $updated++;

                if ($reportMissing && ! ProductMediaPath::existsOnDisk($after)) {
                    $this->warn("  ↳ file still missing on disk: {$after}");
                    $missing++;
                }
            }
        });

        $this->newLine();
        $this->info($dryRun
            ? "Dry run complete. {$updated} row(s) would be updated."
            : "Done. {$updated} row(s) updated.");

        if ($reportMissing) {
            $this->info("Missing files on disk: {$missing}");
            if ($missing > 0) {
                $this->comment('Re-upload those images in admin, or copy files into storage/app/public/ on the server.');
            }
        }

        return self::SUCCESS;
    }
}
