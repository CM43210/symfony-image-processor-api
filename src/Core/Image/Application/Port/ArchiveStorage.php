<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Port;

use App\Core\Image\Application\View\ProcessedImageDownload;
use App\Core\Image\Domain\ImageId;

interface ArchiveStorage
{
    public function getArchiveForImage(ImageId $imageId): ?ProcessedImageDownload;
    public function archiveExists(ImageId $imageId): bool;
}
