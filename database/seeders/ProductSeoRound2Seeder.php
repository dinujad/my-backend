<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Round-2 query-led SEO (Oct 2026): update the admin seo_title /
 * seo_description of 14 existing products to the query-led copy.
 *
 * Run on the live backend explicitly — this seeder is NOT registered in
 * DatabaseSeeder, so a full `db:seed` will not touch it:
 *
 *     php artisan db:seed --class=ProductSeoRound2Seeder
 *
 * Why a seeder: the frontend product page prefers the backend seo_title /
 * seo_description over the code overrides in src/data/product-seo.ts, so
 * code overrides alone cannot change these pages' live titles/metas.
 * The copy below is identical to the round-2 code overrides, so code
 * fallback and admin agree.
 *
 * Safety notes:
 * - ONLY seo_title and seo_description are touched. Nothing else on the
 *   product is changed — no name, price, status, category, image or stock.
 * - Products are found by slug and UPDATED only. If a slug is missing,
 *   it is reported and skipped — nothing is created.
 * - Re-running is safe: it writes the same 14 values again.
 * - No prices are set anywhere (quote model — never invent a price).
 */
class ProductSeoRound2Seeder extends Seeder
{
    public function run(): void
    {
        $seo = [
            'acrylic-laser-cutting-and-engraving' => [
                'Acrylic Laser Cutting & Engraving in Sri Lanka',
                'Acrylic laser cutting and engraving in Sri Lanka — custom acrylic shapes, signs and displays cut and engraved to your design. Island-wide delivery.',
            ],
            'stainless-steel-house-number-plates' => [
                'Stainless Steel House Number Plates in Sri Lanka',
                'Stainless steel house number plates in Sri Lanka — engraved, weather-resistant plates for homes, gates and walls. Custom sizes and text.',
            ],
            'custom-floor-stickers-and-decals' => [
                'Custom Floor Stickers & Decals in Sri Lanka',
                'Custom floor stickers and decals in Sri Lanka — durable floor graphics for shops, offices and events, made to your design and size.',
            ],
            'uv-dtf-printing' => [
                'UV DTF Printing in Sri Lanka — A3 Sheets & Stickers',
                'UV DTF printing in Sri Lanka — detailed A3 transfer sheets and stickers for applying your design to many surfaces. Island-wide delivery.',
            ],
            'corporate-and-student-id-cards' => [
                'ID Card Printing in Sri Lanka — Corporate & Student Cards',
                'ID card printing in Sri Lanka for companies, schools and events — corporate and student ID cards printed to your design.',
            ],
            'acrylic-suggestions-box-slim-series' => [
                'Acrylic Suggestion Box in Sri Lanka — Slim Series',
                'Acrylic suggestion boxes in Sri Lanka — slim-series complaint and suggestion boxes for offices, hotels and clinics.',
            ],
            'acrylic-name-badge' => [
                'Acrylic Name Badges in Sri Lanka',
                'Acrylic name badges in Sri Lanka — custom staff, event and company name badges printed with your logo and names.',
            ],
            'acrylic-name-plate-holder-removable-type' => [
                'Acrylic Name Plate Holders in Sri Lanka',
                'Acrylic name plate holders in Sri Lanka — removable-type desk name plates for offices, reception desks and events.',
            ],
            'wooden-coasters' => [
                'Wooden Coasters in Sri Lanka',
                'Wooden coasters in Sri Lanka — custom engraved and printed coasters for homes, cafes, hotels and corporate gifts.',
            ],
            'qr-code-stands' => [
                'QR Code Stands in Sri Lanka',
                'QR code stands in Sri Lanka — acrylic stands for menus, payments and promotions in restaurants, shops and events.',
            ],
            'acrylic-key-tags' => [
                'Acrylic Key Tags Printing in Sri Lanka',
                'Acrylic key tags in Sri Lanka — custom printed key tags for hotels, gyms, clubs and businesses, with your logo.',
            ],
            'printed-transparent-stickers' => [
                'Transparent Sticker Printing in Sri Lanka',
                'Transparent sticker printing in Sri Lanka — clear stickers for products, packaging and branding that show the surface beneath.',
            ],
            'custom-vinyl-decals' => [
                'Custom Vinyl Decals & Vinyl Stickers in Sri Lanka',
                'Custom vinyl decals in Sri Lanka — durable vinyl stickers for vehicles, shopfronts, walls and products, cut to your design.',
            ],
            'uv-printing-on-glass-ceramic' => [
                'UV Printing on Glass & Ceramic in Sri Lanka',
                'UV printing on glass and ceramic in Sri Lanka — decorative panels, glass signage and personalised ceramic pieces.',
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
