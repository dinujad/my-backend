<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        $files = Media::with('user')->latest()->paginate(24);

        return view('admin.media.index', compact('files'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf',
            'alt_text' => 'nullable|string|max:255',
            'folder' => 'nullable|string|max:100',
        ]);

        $file = $request->file('file');
        $folder = trim($request->input('folder', 'uploads'), '/');
        $path = $file->store($folder, 'public');

        $dimensions = @getimagesize($file->getRealPath());

        $media = Media::create([
            'user_id' => auth()->id(),
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'alt_text' => $request->input('alt_text', $file->getClientOriginalName()),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions ? $dimensions[0] : null,
            'height' => $dimensions ? $dimensions[1] : null,
            'folder' => '/' . $folder,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['url' => $media->url, 'id' => $media->id]);
        }

        return redirect()->route('admin.media.index')->with('success', 'File uploaded.');
    }

    public function update(Request $request, Media $medium): RedirectResponse
    {
        $request->validate(['alt_text' => 'nullable|string|max:255']);
        $medium->update(['alt_text' => $request->input('alt_text')]);

        return back()->with('success', 'Alt text updated.');
    }

    public function destroy(Media $medium): RedirectResponse
    {
        Storage::disk('public')->delete($medium->path);
        $medium->delete();

        return redirect()->route('admin.media.index')->with('success', 'File deleted.');
    }
}
