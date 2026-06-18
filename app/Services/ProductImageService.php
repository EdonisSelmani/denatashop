<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductImageService
{
    public function store(UploadedFile $file, string $directory = 'products'): string
    {
        $path = $file->store($directory, 'public');
        $this->makeThumbnail($path);

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('public')->delete($path);
        Storage::disk('public')->delete($this->thumbnailPath($path));
    }

    public function makeThumbnail(string $path): void
    {
        if (! extension_loaded('gd')) {
            return;
        }

        $sourcePath = storage_path('app/public/' . $path);

        if (! is_file($sourcePath)) {
            return;
        }

        $imageInfo = getimagesize($sourcePath);

        if (! $imageInfo) {
            return;
        }

        [$width, $height, $type] = $imageInfo;

        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default => false,
        };

        if (! $source) {
            return;
        }

        $maxSize = 640;
        $ratio = min($maxSize / $width, $maxSize / $height, 1);
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        $thumb = imagecreatetruecolor($targetWidth, $targetHeight);
        imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255));
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $thumbnailPath = storage_path('app/public/' . $this->thumbnailPath($path));
        $thumbnailDirectory = dirname($thumbnailPath);

        if (! is_dir($thumbnailDirectory)) {
            mkdir($thumbnailDirectory, 0755, true);
        }

        imagewebp($thumb, $thumbnailPath, 78);
        imagedestroy($source);
        imagedestroy($thumb);
    }

    public function thumbnailPath(string $path): string
    {
        $pathInfo = pathinfo($path);

        return 'product-thumbs/' . ($pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] . '/' : '') . $pathInfo['filename'] . '.webp';
    }
}
