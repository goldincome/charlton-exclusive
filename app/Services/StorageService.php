<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class StorageService
{
    public const DISK = 'private';

    /**
     * Store an uploaded image on the private disk with a random name, stripping
     * EXIF data and generating a WebP thumbnail.
     *
     * @return array{path:string, thumbnail:string}
     */
    public function storeImage(UploadedFile $file, string $directory, int $maxWidth = 2000): array
    {
        $this->assertImage($file);

        $name = Str::random(40).'.webp';
        $path = trim($directory, '/').'/'.$name;

        $manager = new ImageManager(new Driver);
        $image = $manager->read($file->getRealPath())->scaleDown(width: $maxWidth);

        Storage::disk(self::DISK)->put($path, (string) $image->toWebp(82));

        $thumbName = pathinfo($name, PATHINFO_FILENAME).'-thumb.webp';
        $thumbPath = trim($directory, '/').'/'.$thumbName;
        $thumb = $manager->read($file->getRealPath())->scaleDown(width: 600);
        Storage::disk(self::DISK)->put($thumbPath, (string) $thumb->toWebp(78));

        return ['path' => $path, 'thumbnail' => $thumbPath];
    }

    public function storeDocument(UploadedFile $file, string $directory): string
    {
        $name = Str::random(40).'.'.$file->getClientOriginalExtension();
        $path = trim($directory, '/').'/'.$name;

        Storage::disk(self::DISK)->putFileAs(trim($directory, '/'), $file, $name);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public function exists(string $path): bool
    {
        return Storage::disk(self::DISK)->exists($path);
    }

    public function response(string $path)
    {
        abort_unless($this->exists($path), 404);

        return Storage::disk(self::DISK)->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function assertImage(UploadedFile $file): void
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        abort_unless(in_array($file->getMimeType(), $allowed, true), 422, 'Unsupported image type.');
        abort_unless($file->getSize() <= 10 * 1024 * 1024, 422, 'Images must be 10 MB or smaller.');
    }
}
