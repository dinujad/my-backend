<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categorySlugMap = [
            'Custom acrylic products & creations' => 'custom-acrylic-products',
            'Sticker printing & cutting' => 'sticker-printing-cutting',
            'UV Flatbed Printing' => 'uv-flatbed-printing',
            'Signage boards' => 'signage-boards',
            'PVC card printing' => 'pvc-card-printing',
        ];

        $products = [
            [
                'name' => 'Acrylic Display Cubes',
                'slug' => 'acrylic-display-cubes',
                'category_name' => 'Custom acrylic products & creations',
                'description' => 'Premium custom acrylic display cubes for retail and exhibitions. UV printed with your design. Multiple sizes available. Island-wide delivery in Sri Lanka.',
                'price' => 4475.00,
                'compare_price' => null,
                'image' => '/images/products/product_cube_1771959614449.png',
                'badge' => '-6%',
                'sku' => 'PW-0001',
                'variants_note' => 'This product has multiple variants. The options may be chosen on the product page.',
            ],
            [
                'name' => 'Custom Laptop Stickers',
                'slug' => 'custom-laptop-stickers',
                'category_name' => 'Sticker printing & cutting',
                'description' => 'Custom laptop stickers and decals. Your design, die-cut or kiss-cut. Fast turnaround. Ideal for brands and creators in Sri Lanka.',
                'price' => 1250.00,
                'compare_price' => null,
                'image' => '/images/products/product_sticker_1771959687979.png',
                'badge' => null,
                'sku' => 'PW-0002',
                'variants_note' => null,
            ],
            [
                'name' => 'Table Top Notice Holder',
                'slug' => 'table-top-notice-holder',
                'category_name' => 'Custom acrylic products & creations',
                'description' => 'Table top notice holder in acrylic. Perfect for menus, price lists and information stands. Custom printing available.',
                'price' => 3990.00,
                'compare_price' => null,
                'image' => '/images/products/product_holder_1771959717699.png',
                'badge' => null,
                'sku' => 'PW-0003',
                'variants_note' => null,
            ],
            [
                'name' => 'UV Flatbed Printing',
                'slug' => 'uv-flatbed-printing',
                'category_name' => 'UV Flatbed Printing',
                'description' => 'UV flatbed printing on a variety of substrates. Sharp, durable prints for signage and display. Sri Lanka-wide service.',
                'price' => 2500.00,
                'compare_price' => null,
                'image' => '/images/products/product_metal_1771959747350.png',
                'badge' => null,
                'sku' => 'PW-0004',
                'variants_note' => null,
            ],
            [
                'name' => 'UV Printing on Metal',
                'slug' => 'uv-printing-on-metal',
                'category_name' => 'UV Flatbed Printing',
                'description' => 'UV printing on metal for nameplates, signage and industrial labels. Durable and professional finish.',
                'price' => 5750.00,
                'compare_price' => null,
                'image' => '/images/products/product_metal_1771959747350.png',
                'badge' => null,
                'sku' => 'PW-0005',
                'variants_note' => null,
            ],
            [
                'name' => 'Acrylic Name Tag Holders',
                'slug' => 'acrylic-name-tag-holders',
                'category_name' => 'Custom acrylic products & creations',
                'description' => 'Acrylic name tag holders for events and offices. Clear or coloured. Bulk orders welcome.',
                'price' => 1890.00,
                'compare_price' => null,
                'image' => '/images/products/product_cube_1771959614449.png',
                'badge' => null,
                'sku' => 'PW-0006',
                'variants_note' => null,
            ],
            [
                'name' => 'Signage Boards',
                'slug' => 'signage-boards',
                'category_name' => 'Signage boards',
                'description' => 'Indoor and outdoor signage boards. Retail, corporate and safety signage. Custom sizes and finishes.',
                'price' => 4200.00,
                'compare_price' => null,
                'image' => '/images/products/product_holder_1771959717699.png',
                'badge' => null,
                'sku' => 'PW-0007',
                'variants_note' => null,
            ],
            [
                'name' => 'PVC Card Printing',
                'slug' => 'pvc-card-printing',
                'category_name' => 'PVC card printing',
                'description' => 'PVC card printing for ID cards, visitor passes and business cards. Fast turnaround. Island-wide delivery.',
                'price' => 850.00,
                'compare_price' => null,
                'image' => '/images/products/product_cube_1771959614449.png',
                'badge' => null,
                'sku' => 'PW-0008',
                'variants_note' => null,
            ],
        ];

        foreach ($products as $index => $data) {
            $categoryName = $data['category_name'];
            $categorySlug = $categorySlugMap[$categoryName] ?? null;
            unset($data['category_name']);

            $categoryId = null;
            if ($categorySlug) {
                $category = Category::where('slug', $categorySlug)->first();
                $categoryId = $category?->id;
            }

            Product::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'category_id' => $categoryId,
                    'is_active' => true,
                    'is_featured' => in_array($data['slug'], ['acrylic-display-cubes', 'custom-laptop-stickers'], true),
                    'sort_order' => $index + 1,
                ])
            );
        }
    }
}
