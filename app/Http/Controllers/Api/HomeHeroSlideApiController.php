<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeHeroSlide;
use Illuminate\Http\JsonResponse;

class HomeHeroSlideApiController extends Controller
{
    public function index(): JsonResponse
    {
        $slides = HomeHeroSlide::activeOrdered()
            ->get()
            ->map(fn (HomeHeroSlide $s) => [
                'id' => $s->id,
                'eyebrow' => $s->eyebrow,
                'title_line1' => $s->title_line1,
                'title_line2' => $s->title_line2,
                'highlight_text' => $s->highlight_text,
                'description' => $s->description,
                'cta_label' => $s->cta_label,
                'cta_url' => $s->cta_url,
                'image' => $s->imageUrl(),
            ]);

        return response()->json($slides);
    }
}
