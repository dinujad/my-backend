<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingDistrict;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Services\ShippingService;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(private ShippingService $shippingService) {}

    // -------------------------------------------------------
    // ZONES
    // -------------------------------------------------------

    public function index()
    {
        $zones   = ShippingZone::with(['methods', 'districts'])->orderBy('sort_order')->get();
        $districts = ShippingDistrict::with('zone')->orderBy('province')->orderBy('name')->get();

        return view('admin.shipping.index', compact('zones', 'districts'));
    }

    public function createZone()
    {
        return view('admin.shipping.zone-form', ['zone' => null]);
    }

    public function storeZone(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:shipping_zones,name',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);

        ShippingZone::create($data);
        $this->shippingService->clearCache();

        return redirect()->route('admin.shipping.index')->with('success', 'Shipping zone created.');
    }

    public function editZone(ShippingZone $zone)
    {
        $zone->load(['methods', 'districts']);
        $allDistricts = ShippingDistrict::orderBy('province')->orderBy('name')->get();

        return view('admin.shipping.zone-form', compact('zone', 'allDistricts'));
    }

    public function updateZone(Request $request, ShippingZone $zone)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:shipping_zones,name,' . $zone->id,
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
            'district_ids' => 'nullable|array',
            'district_ids.*' => 'integer|exists:shipping_districts,id',
        ]);

        $zone->update($data);

        // Assign districts to zone
        if (isset($data['district_ids'])) {
            ShippingDistrict::whereIn('id', $data['district_ids'])
                ->update(['shipping_zone_id' => $zone->id]);
            // Unassign removed districts
            ShippingDistrict::where('shipping_zone_id', $zone->id)
                ->whereNotIn('id', $data['district_ids'])
                ->update(['shipping_zone_id' => null]);
        }

        $this->shippingService->clearCache();

        return redirect()->route('admin.shipping.index')->with('success', 'Zone updated.');
    }

    public function destroyZone(ShippingZone $zone)
    {
        $zone->delete();
        $this->shippingService->clearCache();

        return redirect()->route('admin.shipping.index')->with('success', 'Zone deleted.');
    }

    // -------------------------------------------------------
    // METHODS
    // -------------------------------------------------------

    public function createMethod()
    {
        $zones = ShippingZone::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.shipping.method-form', ['method' => null, 'zones' => $zones]);
    }

    public function storeMethod(Request $request)
    {
        $data = $request->validate([
            'shipping_zone_id'        => 'required|exists:shipping_zones,id',
            'name'                    => 'required|string|max:100',
            'description'             => 'nullable|string|max:500',
            'base_price'              => 'required|numeric|min:0',
            'estimated_days'          => 'nullable|string|max:60',
            'is_active'               => 'boolean',
            'is_free'                 => 'boolean',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'sort_order'              => 'integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_free']   = $request->boolean('is_free');

        ShippingMethod::create($data);
        $this->shippingService->clearCache();

        return redirect()->route('admin.shipping.index')->with('success', 'Shipping method created.');
    }

    public function editMethod(ShippingMethod $method)
    {
        $zones = ShippingZone::orderBy('sort_order')->get();
        $districts = ShippingDistrict::where('shipping_zone_id', $method->shipping_zone_id)
            ->orderBy('name')->get();

        $rates = ShippingRate::where('shipping_method_id', $method->id)
            ->with('district')
            ->get()
            ->keyBy('shipping_district_id');

        return view('admin.shipping.method-form', compact('method', 'zones', 'districts', 'rates'));
    }

    public function updateMethod(Request $request, ShippingMethod $method)
    {
        $data = $request->validate([
            'shipping_zone_id'        => 'required|exists:shipping_zones,id',
            'name'                    => 'required|string|max:100',
            'description'             => 'nullable|string|max:500',
            'base_price'              => 'required|numeric|min:0',
            'estimated_days'          => 'nullable|string|max:60',
            'is_active'               => 'boolean',
            'is_free'                 => 'boolean',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'sort_order'              => 'integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_free']   = $request->boolean('is_free');

        $method->update($data);

        // Save district-level rate overrides
        $ratesPrices    = $request->input('rates', []);    // [district_id => price]
        $ratesFree      = $request->input('rates_free', []); // [district_id => 1]
        $ratesThreshold = $request->input('rates_threshold', []); // [district_id => amount]

        foreach ($ratesPrices as $districtId => $price) {
            if ($price === '' || $price === null) {
                ShippingRate::where('shipping_method_id', $method->id)
                    ->where('shipping_district_id', $districtId)
                    ->delete();
                continue;
            }

            ShippingRate::updateOrCreate(
                ['shipping_method_id' => $method->id, 'shipping_district_id' => $districtId],
                [
                    'price'                    => (float) $price,
                    'is_free'                  => isset($ratesFree[$districtId]),
                    'free_shipping_threshold'  => $ratesThreshold[$districtId] ?? null,
                ]
            );
        }

        $this->shippingService->clearCache();

        return redirect()->route('admin.shipping.index')->with('success', 'Method updated.');
    }

    public function destroyMethod(ShippingMethod $method)
    {
        $method->delete();
        $this->shippingService->clearCache();

        return redirect()->route('admin.shipping.index')->with('success', 'Method deleted.');
    }
}
