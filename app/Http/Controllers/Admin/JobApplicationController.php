<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $query = JobApplication::with('jobOpening')->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('position_applied', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(20)->withQueryString();

        return view('admin.job-applications.index', compact('applications'));
    }

    public function show(JobApplication $job_application): View
    {
        $job_application->load('jobOpening');

        return view('admin.job-applications.show', ['application' => $job_application]);
    }

    public function updateStatus(Request $request, JobApplication $job_application): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(JobApplication::STATUSES)),
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $job_application->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? $job_application->admin_notes,
        ]);

        return back()->with('success', 'Application updated.');
    }
}
