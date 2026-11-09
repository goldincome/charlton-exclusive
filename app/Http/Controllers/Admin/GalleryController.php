<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Services\StorageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    public function __construct(private StorageService $storage)
    {
    }

    public function index(): View
    {
        return view('admin.gallery.index', [
            'albums' => GalleryAlbum::query()->withCount('photos')->get(),
            'photos' => GalleryPhoto::query()->latest()->paginate(24),
        ]);
    }

    public function storeAlbum(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'event_id' => ['nullable', 'exists:events,id'],
        ]);

        GalleryAlbum::query()->create($data + ['slug' => Str::slug($data['name'])]);

        return back()->with('status', 'Album created.');
    }

    public function upload(Request $request, GalleryAlbum $album): RedirectResponse
    {
        $request->validate([
            'photos' => ['required', 'array'],
            'photos.*' => ['image', 'max:10240'],
            'visibility' => ['required', 'in:public,members_only'],
            'alt_text' => ['nullable', 'string', 'max:190'],
        ]);

        foreach ($request->file('photos') as $file) {
            $stored = $this->storage->storeImage($file, 'gallery/'.$album->id);

            GalleryPhoto::query()->create([
                'gallery_album_id' => $album->id,
                'path' => $stored['path'],
                'thumbnail_path' => $stored['thumbnail'],
                'visibility' => $request->input('visibility'),
                'alt_text' => $request->input('alt_text') ?: $album->name,
                'title' => $album->name,
            ]);
        }

        return back()->with('status', 'Photos uploaded.');
    }

    public function updateVisibility(Request $request, GalleryPhoto $photo): RedirectResponse
    {
        $data = $request->validate(['visibility' => ['required', 'in:public,members_only']]);
        $photo->update($data);

        return back()->with('status', 'Photo visibility updated.');
    }

    public function destroyPhoto(GalleryPhoto $photo): RedirectResponse
    {
        $this->storage->delete($photo->path);
        $this->storage->delete($photo->thumbnail_path);
        $photo->delete();

        return back()->with('status', 'Photo deleted.');
    }
}