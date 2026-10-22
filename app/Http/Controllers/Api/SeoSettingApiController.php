<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\JsonResponse;

class SeoSettingApiController extends Controller
{
    /**
     * Fields a public client may read. Do not add tracking, verification, or other settings.
     */
    private const PUBLIC_KEYS = [
        'site_title',
        'site_description',
    ];

    public function show(): JsonResponse
    {
        $payload = [];
        foreach (self::PUBLIC_KEYS as $key) {
            $payload[$key] = SeoSetting::get($key);
        }

        return response()
            ->json($payload)
            ->header('Cache-Control', 'no-store');
    }
}
