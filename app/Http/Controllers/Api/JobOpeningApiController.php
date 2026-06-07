<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use Illuminate\Http\JsonResponse;

class JobOpeningApiController extends Controller
{
    public function index(): JsonResponse
    {
        $openings = JobOpening::activeOrdered()->get()->map(fn (JobOpening $o) => $this->format($o));

        return response()->json($openings);
    }

    private function format(JobOpening $o): array
    {
        return [
            'id' => $o->id,
            'title' => $o->title,
            'slug' => $o->slug,
            'location' => $o->location,
            'employment_type' => $o->employment_type,
            'summary' => $o->summary,
            'requirements' => $o->requirementsList(),
        ];
    }
}
