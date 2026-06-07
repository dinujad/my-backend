<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JobOpeningController extends Controller
{
    public function index(): View
    {
        $openings = JobOpening::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.job-openings.index', compact('openings'));
    }

    public function create(): View
    {
        $opening = new JobOpening([
            'location' => 'Biyagama',
            'sort_order' => (JobOpening::max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        return view('admin.job-openings.create', compact('opening'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? Str::slug($data['title']));

        JobOpening::create($data);

        return redirect()->route('admin.job-openings.index')->with('success', 'Job opening created.');
    }

    public function edit(JobOpening $job_opening): View
    {
        return view('admin.job-openings.edit', ['opening' => $job_opening]);
    }

    public function update(Request $request, JobOpening $job_opening): RedirectResponse
    {
        $data = $this->validated($request, $job_opening->id);
        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?? Str::slug($data['title']),
            $job_opening->id
        );

        $job_opening->update($data);

        return redirect()->route('admin.job-openings.index')->with('success', 'Job opening updated.');
    }

    public function destroy(JobOpening $job_opening): RedirectResponse
    {
        $job_opening->delete();

        return redirect()->route('admin.job-openings.index')->with('success', 'Job opening deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'nullable|string|max:160|unique:job_openings,slug';
        if ($ignoreId) {
            $slugRule .= ',' . $ignoreId;
        }

        return $request->validate([
            'title' => 'required|string|max:200',
            'slug' => $slugRule,
            'location' => 'nullable|string|max:120',
            'employment_type' => 'nullable|string|max:80',
            'summary' => 'nullable|string|max:2000',
            'requirements' => 'nullable|string|max:10000',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug) ?: 'job-opening';
        $candidate = $base;
        $i = 1;

        while (
            JobOpening::where('slug', $candidate)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base . '-' . $i;
            $i++;
        }

        return $candidate;
    }
}
