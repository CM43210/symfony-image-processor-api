<?php

declare(strict_types=1);

namespace App\Infrastructure\Image\Processing;

use App\Core\Image\Application\Port\ImageProcessor;

final class GdImageProcessor implements ImageProcessor
{
    public function resize(
        string $sourcePath,
        string $targetPath,
        int $maxWidth,
        int $maxHeight,
        int $quality = 90
    ): void {
        if (!file_exists($sourcePath)) {
            throw new \RuntimeException("Source file not found: {$sourcePath}");
        }

        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            throw new \RuntimeException("Cannot read image: {$sourcePath}");
        }

        [$width, $height, $type] = $imageInfo;

        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default => throw new \RuntimeException("Unsupported image type: {$type}"),
        };

        if ($source === false) {
            throw new \RuntimeException("Failed to create image resource from: {$sourcePath}");
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        
        if ($ratio >= 1) {
            $newWidth = $width;
            $newHeight = $height;
        } else {
            $newWidth = (int) round($width * $ratio);
            $newHeight = (int) round($height * $ratio);
        }

        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($destination === false) {
            imagedestroy($source);
            throw new \RuntimeException("Failed to create destination image");
        }

        if ($type === IMAGETYPE_PNG) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 0, 0, 0, 127);
            imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled(
            $destination,
            $source,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            imagedestroy($source);
            imagedestroy($destination);
            throw new \RuntimeException("Failed to create directory: {$targetDir}");
        }

        $extension = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        $saved = match ($extension) {
            'jpg', 'jpeg' => imagejpeg($destination, $targetPath, $quality),
            'png' => imagepng($destination, $targetPath, (int) (9 - ($quality / 10))),
            'webp' => imagewebp($destination, $targetPath, $quality),
            default => throw new \RuntimeException("Unsupported target format: {$extension}"),
        };

        imagedestroy($source);
        imagedestroy($destination);

        if (!$saved) {
            throw new \RuntimeException("Failed to save resized image to: {$targetPath}");
        }
    }

    public function convertToWebP(string $sourcePath, string $targetPath, int $quality = 85): void
    {
        if (!file_exists($sourcePath)) {
            throw new \RuntimeException("Source file not found: {$sourcePath}");
        }

        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            throw new \RuntimeException("Cannot read image: {$sourcePath}");
        }

        [, , $type] = $imageInfo;

        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default => throw new \RuntimeException("Unsupported image type: {$type}"),
        };

        if ($image === false) {
            throw new \RuntimeException("Failed to create image resource from: {$sourcePath}");
        }

        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            imagedestroy($image);
            throw new \RuntimeException("Failed to create directory: {$targetDir}");
        }

        $saved = imagewebp($image, $targetPath, $quality);
        imagedestroy($image);

        if (!$saved) {
            throw new \RuntimeException("Failed to save WebP image to: {$targetPath}");
        }
    }

    public function removeMetadata(string $path): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        $imageInfo = getimagesize($path);
        if ($imageInfo === false) {
            throw new \RuntimeException("Cannot read image: {$path}");
        }

        [, , $type] = $imageInfo;

        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => throw new \RuntimeException("Unsupported image type: {$type}"),
        };

        if ($image === false) {
            throw new \RuntimeException("Failed to create image resource from: {$path}");
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $saved = match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $path, 90),
            'png' => imagepng($image, $path, 9),
            'webp' => imagewebp($image, $path, 85),
            default => throw new \RuntimeException("Unsupported format: {$extension}"),
        };

        imagedestroy($image);

        if (!$saved) {
            throw new \RuntimeException("Failed to save image without metadata: {$path}");
        }
    }
}
