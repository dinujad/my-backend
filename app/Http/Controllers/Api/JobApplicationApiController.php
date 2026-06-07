<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Support\ProductMediaPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobApplicationApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'job_opening_id' => 'nullable|integer|exists:job_openings,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:180',
            'phone' => 'required|string|max:30',
            'street_address' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'position_applied' => 'required|string|max:200',
            'cover_letter' => 'nullable|string|max:10000',
            'employment_history' => 'nullable|array|max:20',
            'employment_history.*.employer' => 'nullable|string|max:200',
            'employment_history.*.dates' => 'nullable|string|max:120',
            'employment_history.*.position' => 'nullable|string|max:200',
            'employment_history.*.phone' => 'nullable|string|max:30',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $jobOpeningId = $data['job_opening_id'] ?? null;

        if ($jobOpeningId) {
            $opening = JobOpening::where('id', $jobOpeningId)->where('is_active', true)->first();
            if (!$opening) {
                return response()->json(['message' => 'Selected position is no longer available.'], 422);
            }
        }

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = ProductMediaPath::storeUpload($request->file('resume'), 'careers/resumes');
        }

        $history = collect($data['employment_history'] ?? [])
            ->filter(fn ($row) => is_array($row) && trim((string) ($row['employer'] ?? '')) !== '')
            ->values()
            ->all();

        $application = JobApplication::create([
            'job_opening_id' => $jobOpeningId,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'street_address' => $data['street_address'] ?? null,
            'address_line_2' => $data['address_line_2'] ?? null,
            'position_applied' => $data['position_applied'],
            'employment_history' => $history ?: null,
            'cover_letter' => $data['cover_letter'] ?? null,
            'resume_path' => $resumePath,
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'Your application has been submitted successfully. Our team will review it and get back to you soon.',
            'id' => $application->id,
        ], 201);
    }
}
