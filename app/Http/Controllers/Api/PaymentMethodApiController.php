<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentMethodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodApiController extends Controller
{
    public function __construct(private PaymentMethodService $service) {}

    /**
     * POST /api/payment-methods/for-cart
     * Body: { items: [{ product_id: 1 }, ...] }
     * Returns the intersection of allowed payment methods for these products.
     */
    public function forCart(Request $request): JsonResponse
    {
        $items = $request->input('items', []);

        // Accept JSON string (like checkout items payload) or array
        if (is_string($items)) {
            $items = json_decode($items, true) ?? [];
        }

        $methods = $this->service->getAllowedForCart($items);

        return response()->json([
            'methods' => $methods->map(fn ($m) => [
                'id'          => $m->id,
                'code'        => $m->code,
                'name'        => $m->name,
                'description' => $m->description,
                'type'        => $m->type,
            ])->values(),
        ]);
    }

    /**
     * GET /api/payment-methods
     * Returns all active payment methods (no cart context).
     */
    public function index(): JsonResponse
    {
        $methods = $this->service->getActive();

        return response()->json([
            'methods' => $methods->map(fn ($m) => [
                'id'          => $m->id,
                'code'        => $m->code,
                'name'        => $m->name,
                'description' => $m->description,
                'type'        => $m->type,
            ])->values(),
        ]);
    }
}
