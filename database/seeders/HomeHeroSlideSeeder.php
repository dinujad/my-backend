<?php

namespace Database\Seeders;

use App\Models\HomeHeroSlide;
use Illuminate\Database\Seeder;

class HomeHeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        if (HomeHeroSlide::exists()) {
            return;
        }

        HomeHeroSlide::create([
            'eyebrow' => 'Premium Quality Printing',
            'title_line1' => 'Make Every Detail',
            'title_line2' => 'Stand Out',
            'highlight_text' => 'UP TO 40% OFF',
            'description' => "Sri Lanka's leading digital and offset printing solution. Quality that speaks for itself.",
            'cta_label' => 'Start Buying',
            'cta_url' => '/products',
            'image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?q=80&w=1000&auto=format&fit=crop',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }
}
