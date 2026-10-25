<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * CNC Cutting & Engraving category + 10 service pages (Oct 2026 SEO handover).
 *
 * Run on the live backend explicitly — this seeder is NOT registered in
 * DatabaseSeeder, so a full `db:seed` will not touch it:
 *
 *     php artisan db:seed --class=CncCategorySeeder
 *
 * Safety notes:
 * - Category is created/updated by slug (same pattern as CategorySeeder).
 * - Products use firstOrCreate by slug, so re-running this seeder NEVER
 *   reverts an admin change (e.g. publishing a product after photos arrive).
 * - Products are created as DRAFT with no price and no image: nothing goes
 *   public from this seeder alone. Publish each product from the admin panel
 *   once its genuine workshop photo is attached, and set the category live
 *   only when at least the first products are published.
 * - No prices are set anywhere (quote model — never invent a price).
 */
class CncCategorySeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::updateOrCreate(
            ['slug' => 'cnc-cutting-engraving'],
            [
                'name' => 'CNC Cutting & Engraving',
                'description' => 'CNC cutting and engraving for MDF, plywood, wood, Eco / PVC foam board and ACP/cladding in Sri Lanka. Send your design to PrintWorks.lk for a quotation.',
                'seo_title' => 'CNC Cutting & Engraving Sri Lanka | PrintWorks.lk',
                'seo_description' => 'CNC cutting and engraving for MDF, plywood, wood, Eco / PVC foam board and ACP/cladding in Sri Lanka. Send your design to PrintWorks.lk for a quotation.',
                'sort_order' => 13,
                'is_active' => true,
            ]
        );

        $products = [
            [
                'name' => 'MDF CNC Cutting',
                'slug' => 'mdf-cnc-cutting',
                'material' => 'MDF',
                'short_description' => 'Custom shapes, lettering, panels and display components cut from MDF.',
                'description' => 'Order custom shapes, lettering, panels and display components cut from MDF. Share your dimensions and intended finish so the design can allow for edge preparation, joints and assembly where required.',
                'seo_title' => 'MDF CNC Cutting Sri Lanka',
                'seo_description' => 'Custom MDF shapes, lettering, panels and display components cut to your design. Send dimensions and finish for a quotation from PrintWorks.lk.',
            ],
            [
                'name' => 'Eco / PVC Foam Board CNC Cutting',
                'slug' => 'foam-board-cnc-cutting',
                'material' => 'Eco / PVC foam board',
                'short_description' => 'Lettering, signage components and decorative shapes from Eco / PVC foam board.',
                'description' => 'Create lettering, signage components and decorative shapes from Eco / PVC foam board. Send the board specification, thickness and intended application so our team can assess the design, cutting details and required finish.',
                'seo_title' => 'Eco / PVC Foam Board CNC Cutting Sri Lanka',
                'seo_description' => 'Eco / PVC foam board lettering, signage components and decorative shapes cut to order. Send your specification for a quotation.',
            ],
            [
                'name' => 'Plywood CNC Cutting',
                'slug' => 'plywood-cnc-cutting',
                'material' => 'Plywood',
                'short_description' => 'Plywood panels, display parts, templates and interlocking components.',
                'description' => 'Produce plywood panels, display parts, templates and interlocking components from your design. Specify the plywood grade, thickness and visible face. Fit requirements and grain direction are checked before production.',
                'seo_title' => 'Plywood CNC Cutting Sri Lanka',
                'seo_description' => 'Custom plywood panels, display parts, templates and interlocking components cut from your design. Send grade and thickness for a quotation.',
            ],
            [
                'name' => 'Cladding CNC Cutting',
                'slug' => 'cladding-cnc-cutting',
                'material' => 'ACP/cladding',
                'short_description' => 'ACP/cladding cut into custom shapes for sign faces and decorative features.',
                'description' => 'Cut ACP/cladding (aluminium composite panels) into custom shapes for sign faces and decorative features. Confirm whether you need cutting only or additional V-grooving for folding. Grooving, folding and installation require separate confirmation.',
                'seo_title' => 'Cladding CNC Cutting Sri Lanka',
                'seo_description' => 'ACP/cladding cut into custom shapes for sign faces and decorative features. Confirm cutting or V-grooving needs for a quotation.',
            ],
            [
                'name' => 'Wood CNC Engraving & Carving',
                'slug' => 'wood-cnc-engraving-carving',
                'material' => 'Wood',
                'short_description' => 'Recessed lettering, logos, patterns and relief carving in suitable wood.',
                'description' => 'Add recessed lettering, logos, patterns or relief carving to suitable wood. The timber, engraving depth and level of detail influence the appearance. Send a reference image and dimensions for a feasibility review.',
                'seo_title' => 'Wood CNC Engraving & Carving Sri Lanka',
                'seo_description' => 'Recessed lettering, logos, patterns and relief carving in suitable wood. Send a reference image and dimensions for a feasibility review.',
            ],
            [
                'name' => 'CNC Cut Letters & Logos',
                'slug' => 'cnc-cut-letters-logos',
                'material' => null,
                'short_description' => 'Individual letters, numbers and brand logos for walls, displays and events.',
                'description' => 'Create individual letters, numbers and brand logos for reception walls, shop displays and event features. Specify the material, letter depth, colour and mounting method so the quotation reflects the required finish.',
                'seo_title' => 'CNC Cut Letters & Logos Sri Lanka',
                'seo_description' => 'Individual CNC-cut letters, numbers and brand logos for reception walls, shop displays and events. Send material and size for a quotation.',
            ],
            [
                'name' => 'Custom Wooden Signs & Nameplates',
                'slug' => 'wooden-signs-nameplates',
                'material' => 'Wood',
                'short_description' => 'Personalised wooden signs and nameplates with names, logos or engraved details.',
                'description' => 'Personalize wooden signs for businesses, homes, rooms and desks with names, logos or engraved details. Discuss the surface finish, installation position and exposure conditions before choosing the wood and treatment.',
                'seo_title' => 'Custom Wooden Signs & Nameplates Sri Lanka',
                'seo_description' => 'Personalised wooden signs and nameplates with names, logos or engraved details for businesses, homes, rooms and desks.',
            ],
            [
                'name' => 'MDF & Plywood Product Display Racks',
                'slug' => 'mdf-plywood-display-racks',
                'material' => 'MDF, Plywood',
                'short_description' => 'Countertop or floor-standing display racks built around your products.',
                'description' => 'Develop countertop or floor-standing display racks around your products. Provide product dimensions, quantities and expected shelf loads. Confirm whether you need cut components, flat-packed parts or an assembled unit, with branding assessed separately.',
                'seo_title' => 'MDF & Plywood Display Racks Sri Lanka',
                'seo_description' => 'Countertop or floor-standing display racks built around your products in MDF or plywood. Send product dimensions for a quotation.',
            ],
            [
                'name' => 'CNC Decorative Panels & Partitions',
                'slug' => 'cnc-decorative-panels-partitions',
                'material' => null,
                'short_description' => 'Patterned screens, wall features and decorative partitions for interiors.',
                'description' => 'Create patterned screens, wall features and decorative partitions for interior projects. Discuss pattern scale, open areas, framing and panel joins. Mounting arrangements and the strength of narrow details should be reviewed before cutting.',
                'seo_title' => 'CNC Decorative Panels & Partitions Sri Lanka',
                'seo_description' => 'Patterned CNC screens, wall features and decorative partitions for interior projects. Discuss pattern scale and framing for a quotation.',
            ],
            [
                'name' => 'Custom CNC Cutting from Your Design',
                'slug' => 'custom-cnc-cutting',
                'material' => null,
                'short_description' => 'Submit your own drawing for custom CNC-cut parts, templates and shapes.',
                'description' => 'Submit your own drawing for custom CNC cutting of parts, templates and shapes. Include dimensions, material details, quantities and any fitting requirements. The artwork is reviewed before the production scope and price are confirmed.',
                'seo_title' => 'Custom CNC Cutting from Your Design Sri Lanka',
                'seo_description' => 'Submit your own drawing for custom CNC-cut parts, templates and shapes. Include dimensions, material and quantities for a quotation.',
            ],
        ];

        foreach ($products as $index => $data) {
            Product::firstOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'category_id' => $category->id,
                    'status' => 'draft',
                    'visibility' => 'shop_search',
                    'stock_status' => 'instock',
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ])
            );
        }
    }
}
