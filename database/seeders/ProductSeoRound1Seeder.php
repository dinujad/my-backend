<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * First-batch product SEO (Oct 2026): update the admin seo_title /
 * seo_description of the original 6 product-SEO pages to the query-led
 * copy from src/data/product-seo.ts (round-1 overrides, Oct 21).
 *
 * Run on the live backend explicitly — this seeder is NOT registered in
 * DatabaseSeeder, so a full `db:seed` will not touch it:
 *
 *     php artisan db:seed --class=ProductSeoRound1Seeder
 *
 * Why a seeder: the frontend product page prefers the backend seo_title /
 * seo_description over the code overrides, so code overrides alone cannot
 * change these pages' live titles/metas. The copy below is identical to
 * the round-1 code overrides, so code fallback and admin agree.
 * Search words covered (SC Oct 12-22, 2026): "sticker printing" (logo
 * stickers page), "laptop stickers", "warranty card", "banner printing" /
 * "flex printing" (flex banner page), "business card printing" (translucent
 * cards page), large-format sticker printing.
 *
 * Safety notes:
 * - ONLY seo_title and seo_description are touched. Nothing else on the
 *   product is changed — no name, price, status, category, image or stock.
 * - Products are found by slug and UPDATED only. If a slug is missing,
 *   it is reported and skipped — nothing is created.
 * - Re-running is safe: it writes the same 6 values again.
 * - No prices are set anywhere (quote model — never invent a price).
 */
class ProductSeoRound1Seeder extends Seeder
{
    public function run(): void
    {
        $seo = [
            'custom-logo-stickers' => [
                'Custom Logo Stickers — Sticker Printing in Sri Lanka',
                'Logo sticker printing in Sri Lanka — transparent, white PVC and UV DTF stickers for products, packaging, laptops and vehicles, with island-wide delivery.',
            ],
            'large-format-sticker-printing' => [
                'Large Format Sticker Printing in Sri Lanka',
                'Large format sticker printing in Sri Lanka for walls, glass and vehicles — durable white or transparent PVC vinyl with a weather-resistant finish.',
            ],
            'custom-laptop-stickers' => [
                'Custom Laptop Stickers in Sri Lanka',
                'Custom laptop stickers in Sri Lanka — white PVC, transparent and UV DTF decals or direct UV printing, durable and cut to your design. Island-wide delivery.',
            ],
            'pvc-warranty-cards' => [
                'PVC Warranty Cards in Sri Lanka — CR80 Printing',
                'PVC warranty card printing in Sri Lanka — standard CR80 size (86 × 54 mm), 0.76 mm thick, with vivid UV print that lasts. Island-wide delivery.',
            ],
            'flex-banner-printing' => [
                'Flex Banner Printing in Sri Lanka',
                'Flex banner printing in Sri Lanka for events, promotions and shopfronts — durable PVC, crisp colours, hemming, eyelets and pole pockets. Island-wide delivery.',
            ],
            'pvc-translucent-business-cards' => [
                'PVC Translucent Business Cards — Business Card Printing in Colombo, Sri Lanka',
                'Business card printing in Colombo, Sri Lanka — frosted translucent PVC cards with durable UV print. Quality prints, island-wide delivery.',
            ],
        ];

        foreach ($seo as $slug => [$title, $description]) {
            $product = Product::where('slug', $slug)->first();

            if (! $product) {
                $this->command->warn("Skipped (not found): {$slug}");
                continue;
            }

            $product->update([
                'seo_title' => $title,
                'seo_description' => $description,
            ]);

            $this->command->info("Updated SEO: {$slug}");
        }
    }
}
