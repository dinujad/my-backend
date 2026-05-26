<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomePromoBanner;
use Illuminate\Http\JsonResponse;

class HomePromoBannerApiController extends Controller
{
    public function index(): JsonResponse
    {
        $banners = HomePromoBanner::activeOrdered()
            ->get()
            ->map(fn (HomePromoBanner $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'bold_text' => $b->bold_text,
                'post_text' => $b->post_text,
                'second_line' => $b->second_line,
                'has_discount' => $b->has_discount,
                'discount_number' => $b->discount_number,
                'action_text' => $b->action_text,
                'href' => $b->href,
                'alt' => $b->image_alt,
                'image' => $b->imageUrl(),
            ]);

        return response()->json($banners);
    }
}
