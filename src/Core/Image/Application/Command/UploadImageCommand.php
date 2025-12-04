<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Command;

use App\Core\Image\Domain\ImageFormat;

final readonly class UploadImageCommand
{
    public function __construct(
        public string $tmpPath,
        public string $originalName,
        public ImageFormat $format,
        public int $sizeInBytes,
    ) {
    }
}
