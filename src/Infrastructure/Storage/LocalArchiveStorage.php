<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Core\Image\Application\Port\ArchiveStorage;
use App\Core\Image\Application\Port\ImageRepositoryInterface;
use App\Core\Image\Application\View\ProcessedImageDownload;
use App\Core\Image\Domain\ImageId;

final readonly class LocalArchiveStorage implements ArchiveStorage
{
    public function __construct(
        private ImageRepositoryInterface $repository,
        private string $archiveDir,
    ) {
    }

    public function getArchiveForImage(ImageId $imageId): ?ProcessedImageDownload
    {
        $image = $this->repository->findById($imageId);
        
        if ($image === null || $image->processedArchivePath() === null) {
            return null;
        }

        $archivePath = $this->archiveDir . '/' . $image->processedArchivePath();
        
        if (!file_exists($archivePath)) {
            return null;
        }

        return new ProcessedImageDownload(
            absolutePath: $archivePath,
            filename: basename($archivePath),
            contentType: 'application/zip'
        );
    }

    public function archiveExists(ImageId $imageId): bool
    {
        return $this->getArchiveForImage($imageId) !== null;
    }
}
