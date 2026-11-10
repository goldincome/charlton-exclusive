<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $visibility = $request->user() ? [GalleryPhoto::PUBLIC, GalleryPhoto::MEMBERS_ONLY] : [GalleryPhoto::PUBLIC];

        return view('public.gallery.index', [
            'albums' => GalleryAlbum::query()->with(['photos' => fn ($q) => $q->whereIn('visibility', $visibility)])->get(),
            'photos' => GalleryPhoto::query()->whereIn('visibility', $visibility)->latest()->paginate(24),
        ]);
    }

    public function album(Request $request, string $slug): View
    {
        $album = GalleryAlbum::query()->where('slug', $slug)->firstOrFail();

        $query = $album->photos();

        if (! $request->user()) {
            $query->where('visibility', GalleryPhoto::PUBLIC);
        }

        return view('public.gallery.album', [
            'album' => $album,
            'photos' => $query->paginate(24),
        ]);
    }
}
