<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryPhoto;
use App\Services\StorageService;

class MediaController extends Controller
{
    public function __construct(private StorageService $storage) {}

    public function gallery(GalleryPhoto $photo)
    {
        if ($photo->isMembersOnly()) {
            abort_unless(auth()->check() && (auth()->user()->isMember() || auth()->user()->isStaffLike()), 403);
        }

        return $this->storage->response($photo->path);
    }

    public function thumbnail(GalleryPhoto $photo)
    {
        if ($photo->isMembersOnly()) {
            abort_unless(auth()->check() && (auth()->user()->isMember() || auth()->user()->isStaffLike()), 403);
        }

        return $this->storage->response($photo->thumbnail_path ?: $photo->path);
    }

    public function publicFile(string $path)
    {
        // Public imagery (rooms, events, menu) lives under the public/ media prefix only.
        abort_unless(str_starts_with($path, 'public/'), 404);

        return $this->storage->response($path);
    }
}
