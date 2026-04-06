<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingApiController extends Controller
{
    public function __construct(private ShippingService $shippingService) {}

    /**
     * GET /api/shipping/districts
     * Returns all 25 districts flat list for dropdown.
     */
    public function districts(): JsonResponse
    {
        $names = $this->shippingService->getDistrictNames();
        return response()->json(['districts' => $names]);
    }

    /**
     * GET /api/shipping/rates?district=Colombo&total=5000
     * Returns available shipping methods + prices for a district.
     */
    public function rates(Request $request): JsonResponse
    {
        $district = $request->query('district', '');
        $total    = (float) $request->query('total', 0);

        if (! $district) {
            return response()->json(['methods' => []]);
        }

        $methods = $this->shippingService->getRatesForDistrict($district, $total);

        return response()->json([
            'district' => $district,
            'methods'  => $methods,
        ]);
    }
}
