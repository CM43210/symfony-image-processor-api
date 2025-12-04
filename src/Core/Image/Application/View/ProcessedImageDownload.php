<?php

declare(strict_types=1);

namespace App\Core\Image\Application\View;

final readonly class ProcessedImageDownload
{
    public function __construct(
        public string $absolutePath,
        public string $filename,
        public string $contentType = 'application/zip',
    ) {
    }
}
