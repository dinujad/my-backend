<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'uv-flatbed-printing', 'name' => 'UV Flatbed Printing', 'description' => 'UV flatbed printing on acrylic, wood, metal, glass and more. High-quality finishes for signage and display.', 'sort_order' => 1],
            ['slug' => 'custom-acrylic-products', 'name' => 'Custom acrylic products & creations', 'description' => 'Custom acrylic display cubes, name tag holders, table top notice holders and bespoke acrylic creations.', 'sort_order' => 2],
            ['slug' => 'sticker-printing-cutting', 'name' => 'Sticker printing & cutting', 'description' => 'Custom stickers, laptop stickers, decals and short-run product labels. Die-cut and kiss-cut.', 'sort_order' => 3],
            ['slug' => 'signage-boards', 'name' => 'Signage boards', 'description' => 'Retail, corporate, safety and wayfinding signage. Indoor and outdoor solutions.', 'sort_order' => 4],
            ['slug' => 'pvc-card-printing', 'name' => 'PVC card printing', 'description' => 'PVC ID cards, visitor passes, business cards and membership cards.', 'sort_order' => 5],
            ['slug' => 'badges-tags', 'name' => 'Badges & Tags', 'description' => 'Name badges, tags and promotional badges.', 'sort_order' => 6],
            ['slug' => 'digital-printing', 'name' => 'Digital printing', 'description' => 'Digital and large-format printing for brochures, flyers and bespoke jobs.', 'sort_order' => 7],
            ['slug' => 'laser-cutting-engraving', 'name' => 'Laser cutting & Engraving', 'description' => 'Laser cutting and engraving on acrylic, wood, metal and more.', 'sort_order' => 8],
            ['slug' => 'graphic-designing', 'name' => 'Graphic Designing', 'description' => 'Logo design, brand identity and print-ready artwork.', 'sort_order' => 9],
            ['slug' => 'promotional-gift-items', 'name' => 'Promotional & Gift items', 'description' => 'Promotional items and corporate gifts.', 'sort_order' => 10],
            ['slug' => 'paper-material-printing', 'name' => 'Paper material printing', 'description' => 'Paper-based printing for bespoke and small batches.', 'sort_order' => 11],
            ['slug' => 'stainless-steel-brass-engraving', 'name' => 'Stainless steel & brass engraving', 'description' => 'Engraving on stainless steel and brass for nameplates and industrial signage.', 'sort_order' => 12],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}
