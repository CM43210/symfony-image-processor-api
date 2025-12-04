<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Query;

use App\Core\Image\Application\Port\ArchiveStorage;
use App\Core\Image\Application\View\ProcessedImageDownload;
use App\Core\Image\Domain\ImageId;

final readonly class DownloadProcessedImage
{
    public function __construct(
        private ArchiveStorage $archiveStorage,
    ) {
    }

    public function __invoke(ImageId $id): ?ProcessedImageDownload
    {
        return $this->archiveStorage->getArchiveForImage($id);
    }
}
