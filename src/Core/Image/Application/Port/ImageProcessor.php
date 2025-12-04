<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Port;

interface ImageProcessor
{
    public function resize(
        string $sourcePath,
        string $targetPath,
        int $maxWidth,
        int $maxHeight,
        int $quality = 90
    ): void;
    public function convertToWebP(string $sourcePath, string $targetPath, int $quality = 85): void;
    public function removeMetadata(string $path): void;
}
