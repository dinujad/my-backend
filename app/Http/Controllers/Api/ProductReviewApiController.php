<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewApiController extends Controller
{
    /**
     * GET /api/v1/products/{slug}/reviews
     */
    public function index(string $slug): JsonResponse
    {
        $product = Product::active()->where('slug', $slug)->firstOrFail();

        if (! $product->enable_reviews) {
            return response()->json([
                'enabled' => false,
                'reviews' => [],
                'summary' => ['average' => 0, 'count' => 0],
            ]);
        }

        $reviews = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->orderByDesc('created_at')
            ->get(['id', 'reviewer_name', 'rating', 'comment', 'created_at']);

        $count = $reviews->count();
        $average = $count > 0 ? round((float) $reviews->avg('rating'), 1) : 0.0;

        return response()->json([
            'enabled' => true,
            'summary' => [
                'average' => $average,
                'count'   => $count,
            ],
            'reviews' => $reviews->map(fn (ProductReview $r) => [
                'id'             => $r->id,
                'reviewer_name'  => $r->reviewer_name,
                'rating'         => (int) $r->rating,
                'comment'        => $r->comment,
                'created_at'     => $r->created_at->toIso8601String(),
            ])->values(),
        ]);
    }

    /**
     * POST /api/v1/products/{slug}/reviews
     * Guest reviews allowed when the product has reviews enabled.
     */
    public function store(Request $request, string $slug): JsonResponse
    {
        $product = Product::active()->where('slug', $slug)->firstOrFail();

        if (! $product->enable_reviews) {
            return response()->json(['message' => 'Reviews are disabled for this product.'], 403);
        }

        $validated = $request->validate([
            'reviewer_name'  => 'required|string|max:120',
            'reviewer_email' => 'nullable|email|max:255',
            'rating'         => 'required|integer|min:1|max:5',
            'comment'        => 'required|string|min:10|max:5000',
        ]);

        $review = ProductReview::create([
            'product_id'     => $product->id,
            'user_id'        => $request->user()?->id,
            'reviewer_name'  => $validated['reviewer_name'],
            'reviewer_email' => $validated['reviewer_email'] ?? null,
            'rating'         => $validated['rating'],
            'comment'        => $validated['comment'],
            'is_approved'    => true,
        ]);

        return response()->json([
            'message' => 'Thank you for your review!',
            'review'  => [
                'id'             => $review->id,
                'reviewer_name'  => $review->reviewer_name,
                'rating'         => (int) $review->rating,
                'comment'        => $review->comment,
                'created_at'     => $review->created_at->toIso8601String(),
            ],
        ], 201);
    }
}
