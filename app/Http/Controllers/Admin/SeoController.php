<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoController extends Controller
{
    private array $fields = [
        'site_title',
        'site_description',
        'site_keywords',
        'og_image',
        'google_analytics_id',
        'google_search_console',
        'robots_txt',
        'canonical_base_url',
        'twitter_handle',
        'facebook_page',
    ];

    public function index(): View
    {
        $settings = [];
        foreach ($this->fields as $key) {
            $settings[$key] = SeoSetting::get($key, '');
        }

        return view('admin.seo.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        foreach ($this->fields as $key) {
            SeoSetting::set($key, $request->input($key));
        }

        return back()->with('success', 'SEO settings saved.');
    }
}
