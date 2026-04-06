<?php

namespace Database\Seeders;

use App\Models\ShippingDistrict;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------
        // Zones
        // -------------------------------------------------------
        $zones = [
            ['name' => 'Western Province',       'description' => 'Colombo, Gampaha, Kalutara',          'sort_order' => 1],
            ['name' => 'Central Province',        'description' => 'Kandy, Matale, Nuwara Eliya',         'sort_order' => 2],
            ['name' => 'Southern Province',       'description' => 'Galle, Matara, Hambantota',           'sort_order' => 3],
            ['name' => 'Northern Province',       'description' => 'Jaffna, Kilinochchi, Mannar, etc.',   'sort_order' => 4],
            ['name' => 'Eastern Province',        'description' => 'Ampara, Batticaloa, Trincomalee',     'sort_order' => 5],
            ['name' => 'North Western Province',  'description' => 'Kurunegala, Puttalam',                'sort_order' => 6],
            ['name' => 'North Central Province',  'description' => 'Anuradhapura, Polonnaruwa',          'sort_order' => 7],
            ['name' => 'Uva Province',            'description' => 'Badulla, Monaragala',                 'sort_order' => 8],
            ['name' => 'Sabaragamuwa Province',   'description' => 'Ratnapura, Kegalle',                  'sort_order' => 9],
        ];

        $zoneMap = [];
        foreach ($zones as $z) {
            $zone = ShippingZone::firstOrCreate(['name' => $z['name']], array_merge($z, ['is_active' => true]));
            $zoneMap[$z['name']] = $zone;
        }

        // -------------------------------------------------------
        // Districts mapped to zones
        // -------------------------------------------------------
        $districts = [
            ['name' => 'Colombo',      'province' => 'Western Province',      'zone' => 'Western Province'],
            ['name' => 'Gampaha',      'province' => 'Western Province',      'zone' => 'Western Province'],
            ['name' => 'Kalutara',     'province' => 'Western Province',      'zone' => 'Western Province'],
            ['name' => 'Kandy',        'province' => 'Central Province',       'zone' => 'Central Province'],
            ['name' => 'Matale',       'province' => 'Central Province',       'zone' => 'Central Province'],
            ['name' => 'Nuwara Eliya', 'province' => 'Central Province',       'zone' => 'Central Province'],
            ['name' => 'Galle',        'province' => 'Southern Province',      'zone' => 'Southern Province'],
            ['name' => 'Matara',       'province' => 'Southern Province',      'zone' => 'Southern Province'],
            ['name' => 'Hambantota',   'province' => 'Southern Province',      'zone' => 'Southern Province'],
            ['name' => 'Jaffna',       'province' => 'Northern Province',      'zone' => 'Northern Province'],
            ['name' => 'Kilinochchi',  'province' => 'Northern Province',      'zone' => 'Northern Province'],
            ['name' => 'Mannar',       'province' => 'Northern Province',      'zone' => 'Northern Province'],
            ['name' => 'Mullaitivu',   'province' => 'Northern Province',      'zone' => 'Northern Province'],
            ['name' => 'Vavuniya',     'province' => 'Northern Province',      'zone' => 'Northern Province'],
            ['name' => 'Ampara',       'province' => 'Eastern Province',       'zone' => 'Eastern Province'],
            ['name' => 'Batticaloa',   'province' => 'Eastern Province',       'zone' => 'Eastern Province'],
            ['name' => 'Trincomalee',  'province' => 'Eastern Province',       'zone' => 'Eastern Province'],
            ['name' => 'Kurunegala',   'province' => 'North Western Province', 'zone' => 'North Western Province'],
            ['name' => 'Puttalam',     'province' => 'North Western Province', 'zone' => 'North Western Province'],
            ['name' => 'Anuradhapura', 'province' => 'North Central Province', 'zone' => 'North Central Province'],
            ['name' => 'Polonnaruwa',  'province' => 'North Central Province', 'zone' => 'North Central Province'],
            ['name' => 'Badulla',      'province' => 'Uva Province',           'zone' => 'Uva Province'],
            ['name' => 'Monaragala',   'province' => 'Uva Province',           'zone' => 'Uva Province'],
            ['name' => 'Ratnapura',    'province' => 'Sabaragamuwa Province',  'zone' => 'Sabaragamuwa Province'],
            ['name' => 'Kegalle',      'province' => 'Sabaragamuwa Province',  'zone' => 'Sabaragamuwa Province'],
        ];

        $districtMap = [];
        foreach ($districts as $d) {
            $zone = $zoneMap[$d['zone']];
            $district = ShippingDistrict::firstOrCreate(
                ['name' => $d['name']],
                ['province' => $d['province'], 'shipping_zone_id' => $zone->id]
            );
            $districtMap[$d['name']] = $district;
        }

        // -------------------------------------------------------
        // Shipping Methods per zone with default prices
        // -------------------------------------------------------

        // Western Province — cheapest (closest to Colombo)
        $westernZone = $zoneMap['Western Province'];
        $standardWest = ShippingMethod::firstOrCreate(
            ['name' => 'Standard Delivery', 'shipping_zone_id' => $westernZone->id],
            [
                'description'             => 'Delivered within 1-2 business days',
                'base_price'              => 350.00,
                'estimated_days'          => '1-2 business days',
                'is_active'               => true,
                'is_free'                 => false,
                'free_shipping_threshold' => 5000.00,
                'sort_order'              => 1,
            ]
        );
        $expressWest = ShippingMethod::firstOrCreate(
            ['name' => 'Express Delivery', 'shipping_zone_id' => $westernZone->id],
            [
                'description'    => 'Same day or next day delivery',
                'base_price'     => 650.00,
                'estimated_days' => 'Same day / Next day',
                'is_active'      => true,
                'is_free'        => false,
                'sort_order'     => 2,
            ]
        );

        // District overrides — Colombo gets cheaper rates
        ShippingRate::firstOrCreate(
            ['shipping_method_id' => $standardWest->id, 'shipping_district_id' => $districtMap['Colombo']->id],
            ['price' => 250.00, 'is_free' => false, 'free_shipping_threshold' => 4000.00]
        );
        ShippingRate::firstOrCreate(
            ['shipping_method_id' => $expressWest->id, 'shipping_district_id' => $districtMap['Colombo']->id],
            ['price' => 500.00, 'is_free' => false]
        );

        // Central Province
        $centralZone = $zoneMap['Central Province'];
        ShippingMethod::firstOrCreate(
            ['name' => 'Standard Delivery', 'shipping_zone_id' => $centralZone->id],
            ['description' => 'Delivered within 2-3 business days', 'base_price' => 500.00, 'estimated_days' => '2-3 business days', 'is_active' => true, 'sort_order' => 1]
        );
        ShippingMethod::firstOrCreate(
            ['name' => 'Express Delivery', 'shipping_zone_id' => $centralZone->id],
            ['description' => 'Next day delivery', 'base_price' => 850.00, 'estimated_days' => 'Next day', 'is_active' => true, 'sort_order' => 2]
        );

        // Southern Province
        $southernZone = $zoneMap['Southern Province'];
        ShippingMethod::firstOrCreate(
            ['name' => 'Standard Delivery', 'shipping_zone_id' => $southernZone->id],
            ['description' => '2-3 business days', 'base_price' => 500.00, 'estimated_days' => '2-3 business days', 'is_active' => true, 'sort_order' => 1]
        );
        ShippingMethod::firstOrCreate(
            ['name' => 'Express Delivery', 'shipping_zone_id' => $southernZone->id],
            ['description' => 'Next day delivery', 'base_price' => 900.00, 'estimated_days' => 'Next day', 'is_active' => true, 'sort_order' => 2]
        );

        // Northern Province
        $northernZone = $zoneMap['Northern Province'];
        ShippingMethod::firstOrCreate(
            ['name' => 'Standard Delivery', 'shipping_zone_id' => $northernZone->id],
            ['description' => '3-5 business days', 'base_price' => 700.00, 'estimated_days' => '3-5 business days', 'is_active' => true, 'sort_order' => 1]
        );

        // Eastern Province
        $easternZone = $zoneMap['Eastern Province'];
        ShippingMethod::firstOrCreate(
            ['name' => 'Standard Delivery', 'shipping_zone_id' => $easternZone->id],
            ['description' => '3-4 business days', 'base_price' => 650.00, 'estimated_days' => '3-4 business days', 'is_active' => true, 'sort_order' => 1]
        );

        // North Western Province
        $nwZone = $zoneMap['North Western Province'];
        ShippingMethod::firstOrCreate(
            ['name' => 'Standard Delivery', 'shipping_zone_id' => $nwZone->id],
            ['description' => '2-3 business days', 'base_price' => 500.00, 'estimated_days' => '2-3 business days', 'is_active' => true, 'sort_order' => 1]
        );

        // North Central Province
        $ncZone = $zoneMap['North Central Province'];
        ShippingMethod::firstOrCreate(
            ['name' => 'Standard Delivery', 'shipping_zone_id' => $ncZone->id],
            ['description' => '2-3 business days', 'base_price' => 550.00, 'estimated_days' => '2-3 business days', 'is_active' => true, 'sort_order' => 1]
        );

        // Uva Province
        $uvaZone = $zoneMap['Uva Province'];
        ShippingMethod::firstOrCreate(
            ['name' => 'Standard Delivery', 'shipping_zone_id' => $uvaZone->id],
            ['description' => '3-4 business days', 'base_price' => 600.00, 'estimated_days' => '3-4 business days', 'is_active' => true, 'sort_order' => 1]
        );

        // Sabaragamuwa Province
        $sabaZone = $zoneMap['Sabaragamuwa Province'];
        ShippingMethod::firstOrCreate(
            ['name' => 'Standard Delivery', 'shipping_zone_id' => $sabaZone->id],
            ['description' => '2-3 business days', 'base_price' => 500.00, 'estimated_days' => '2-3 business days', 'is_active' => true, 'sort_order' => 1]
        );

        $this->command->info('Shipping zones, districts, and methods seeded successfully.');
    }
}
