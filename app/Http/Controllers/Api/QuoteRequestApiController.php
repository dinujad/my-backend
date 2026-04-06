<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Services\QuoteRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuoteRequestApiController extends Controller
{
    public function __construct(private QuoteRequestService $service) {}

    /**
     * Submit a new quote request (public endpoint — guest + logged-in).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name'      => 'required|string|max:120',
            'company_name'       => 'nullable|string|max:120',
            'email'              => 'required|email|max:120',
            'phone'              => 'required|string|max:30',
            'address'            => 'nullable|string|max:500',
            'preferred_contact'  => 'nullable|in:whatsapp,email,phone',
            'preferred_response' => 'nullable|in:whatsapp,email',
            'deadline'           => 'nullable|date',
            'urgency'            => 'nullable|in:normal,urgent,very_urgent',
            'notes'              => 'nullable|string|max:2000',
            'items'              => 'required|array|min:1',
            'items.*.product_name'         => 'required|string|max:250',
            'items.*.product_id'           => 'nullable|integer|exists:products,id',
            'items.*.product_variation_id' => 'nullable|integer|exists:product_variations,id',
            'items.*.product_sku'          => 'nullable|string|max:100',
            'items.*.product_image'        => 'nullable|string|max:500',
            'items.*.variation_attributes' => 'nullable|array',
            'items.*.quantity'             => 'required|integer|min:1|max:99999',
            'items.*.item_notes'           => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customerId = null;
        if ($request->user()) {
            $customer   = \App\Models\Customer::where('user_id', $request->user()->id)->first();
            $customerId = $customer?->id;
        }

        $quoteRequest = $this->service->create($validator->validated(), $customerId);

        return response()->json([
            'message'        => 'Quote request submitted successfully.',
            'request_number' => $quoteRequest->request_number,
            'id'             => $quoteRequest->id,
        ], 201);
    }

    /**
     * Get product suggestions for the quote cart search (public).
     */
    public function productSearch(Request $request)
    {
        $q = $request->input('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $products = Product::active()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%");
            })
            ->with('variations')
            ->select('id', 'name', 'slug', 'sku', 'image', 'price')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'slug'        => $p->slug,
                'sku'         => $p->sku,
                'image'       => $p->image,
                'price'       => $p->price,
                'variations'  => $p->variations->map(fn ($v) => [
                    'id'         => $v->id,
                    'sku'        => $v->sku,
                    'attributes' => $v->attributes,
                ]),
            ]);

        return response()->json($products);
    }

    /**
     * View a quotation by public token (accessible without login).
     */
    public function viewQuotation(string $token)
    {
        $quotation = \App\Models\Quotation::where('public_token', $token)
            ->with(['items', 'quoteRequest'])
            ->firstOrFail();

        // Mark as viewed if first time
        if (!$quotation->viewed_at) {
            $quotation->update(['viewed_at' => now(), 'status' => 'viewed']);
        }

        return response()->json($quotation);
    }
}
