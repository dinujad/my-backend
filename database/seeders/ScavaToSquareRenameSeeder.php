<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Seeder;

/**
 * Scava → Square rename (Nov 2026): the Engraved Metal Pin Badges product
 * (slug engraved-metal-pin-badges-sri-lanka, id 43) still shows shape
 * "Scava" on four variations although the attribute value already reads
 * "Square" in the admin — variations store their own attribute snapshot,
 * so renaming the attribute value did not propagate to them.
 *
 * Run on the live backend explicitly — this seeder is NOT registered in
 * DatabaseSeeder, so a full `db:seed` will not touch it:
 *
 *     php artisan db:seed --class=ScavaToSquareRenameSeeder
 *
 * Safety notes:
 * - ONLY the "Scava" variations of this one product are touched: their
 *   attributes JSON Shape value "Scava" → "Square" and their SKU suffix
 *   "-SCAVA" → "-SQUARE". Nothing else on the product is changed — no
 *   name, price, tiers, status, image or stock.
 * - If a ProductAttributeValue row still carries the value "Scava" for
 *   the Shape attribute it is renamed to "Square" too, so admin and
 *   variations agree.
 * - Products/variations are found by slug and UPDATED only. If the
 *   product is missing, it is reported and skipped — nothing is created.
 * - Re-running is safe: it writes the same values again (no-op).
 * - No prices are set anywhere.
 */
class ScavaToSquareRenameSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::where('slug', 'engraved-metal-pin-badges-sri-lanka')->first();

        if (! $product) {
            $this->command->warn('Product not found: engraved-metal-pin-badges-sri-lanka — nothing changed.');

            return;
        }

        // 1) Attribute value row, if it still reads "Scava".
        $renamedValues = ProductAttributeValue::where('value', 'Scava')
            ->whereHas('attribute', fn ($q) => $q->where('name', 'Shape'))
            ->update(['value' => 'Square']);

        // 2) The variations themselves (their attributes JSON is a snapshot).
        $fixed = 0;
        foreach ($product->variations as $variation) {
            $attrs = $variation->attributes ?? [];
            $shape = $attrs['Shape'] ?? null;
            if (! is_string($shape) || strcasecmp($shape, 'Scava') !== 0) {
                continue;
            }
            $attrs['Shape'] = 'Square';
            $variation->attributes = $attrs;
            if (is_string($variation->sku) && str_contains($variation->sku, '-SCAVA')) {
                $variation->sku = str_replace('-SCAVA', '-SQUARE', $variation->sku);
            }
            $variation->save();
            $fixed++;
        }

        $this->command->info("Scava → Square: {$fixed} variation(s) fixed on '{$product->slug}'; {$renamedValues} attribute value row(s) renamed.");
    }
}
