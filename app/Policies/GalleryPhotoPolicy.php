<?php

namespace App\Policies;

use App\Models\GalleryPhoto;
use App\Models\User;

class GalleryPhotoPolicy
{
    public function view(?User $user, GalleryPhoto $photo): bool
    {
        if ($photo->visibility === GalleryPhoto::PUBLIC) {
            return true;
        }

        return $user !== null && ($user->isMember() || $user->isStaffLike());
    }
}