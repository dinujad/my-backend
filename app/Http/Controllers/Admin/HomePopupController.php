<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomePopup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomePopupController extends Controller
{
    public function index(): View
    {
        $popup = HomePopup::current();

        return view('admin.home-popup.index', [
            'popup' => $popup,
            'imageUrl' => $popup->publicImageUrl(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'close_seconds' => 'required|integer|min:0|max:120',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $popup = HomePopup::current();
        $popup->enabled = $request->boolean('enabled');
        $popup->close_seconds = (int) $data['close_seconds'];

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($popup->image_path);
            $path = $request->file('image')->store('popups', 'public');
            $popup->image_path = 'storage/' . $path;
        } elseif ($request->boolean('remove_image')) {
            $this->deleteStoredImage($popup->image_path);
            $popup->image_path = null;
        }

        $popup->save();

        return back()->with('success', 'Home popup saved.');
    }

    private function deleteStoredImage(?string $path): void
    {
        $path = str_replace('\\', '/', trim((string) $path));
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete(str_replace('storage/', '', ltrim($path, '/')));
    }
}
