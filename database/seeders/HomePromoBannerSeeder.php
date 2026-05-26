<?php

namespace Database\Seeders;

use App\Models\HomePromoBanner;
use Illuminate\Database\Seeder;

class HomePromoBannerSeeder extends Seeder
{
    public function run(): void
    {
        if (HomePromoBanner::exists()) {
            return;
        }

        $defaults = [
            [
                'title' => 'INDUSTRY-GRADE',
                'bold_text' => 'UV FLATBED',
                'second_line' => 'PRINTING',
                'href' => '/products?category=UV+Flatbed',
                'image_alt' => 'UV Printing Promo',
                'image' => '/images/services/services_uv_1771958447138.png',
                'action_text' => 'Shop now',
                'sort_order' => 1,
            ],
            [
                'title' => 'PREMIUM',
                'bold_text' => 'PRODUCTS',
                'second_line' => 'ACRYLIC',
                'has_discount' => true,
                'discount_number' => '20',
                'href' => '/products?category=Acrylic',
                'image_alt' => 'Acrylic Promo',
                'image' => '/images/services/services_acrylic_1771958315597.png',
                'sort_order' => 2,
            ],
            [
                'title' => 'TAILORED',
                'bold_text' => 'CUSTOM',
                'second_line' => 'PRODUCTS',
                'href' => '/products?category=Custom',
                'image_alt' => 'Custom Promo',
                'image' => '/images/services/services_custom_1771958344395.png',
                'action_text' => 'Shop now',
                'sort_order' => 3,
            ],
            [
                'title' => 'ILLUMINATED',
                'bold_text' => 'SIGNAGE',
                'second_line' => '& DISPLAYS',
                'href' => '/products?category=Signage',
                'image_alt' => 'Signage Promo',
                'image' => '/images/services/services_signage_1771958377225.png',
                'action_text' => 'Shop now',
                'sort_order' => 4,
            ],
        ];

        foreach ($defaults as $row) {
            HomePromoBanner::create(array_merge([
                'post_text' => '',
                'has_discount' => false,
                'is_active' => true,
            ], $row));
        }
    }
}
