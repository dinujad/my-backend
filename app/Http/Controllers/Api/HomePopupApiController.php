<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomePopup;
use Illuminate\Http\JsonResponse;

class HomePopupApiController extends Controller
{
    public function show(): JsonResponse
    {
        $popup = HomePopup::current();

        return response()
            ->json([
                'enabled' => (bool) $popup->enabled,
                'image' => $popup->publicImageUrl(),
                'close_seconds' => (int) $popup->close_seconds,
            ])
            ->header('Cache-Control', 'no-store');
    }
}
