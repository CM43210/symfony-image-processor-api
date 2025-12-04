<?php

declare(strict_types=1);

namespace App\Infrastructure\Image\Storage;

use App\Core\Image\Application\Port\TempImageStorage;
use App\Core\Image\Domain\ImageFormat;
use App\Core\Image\Domain\ImageId;
use Symfony\Component\Filesystem\Filesystem;

final readonly class LocalTempImageStorage implements TempImageStorage
{
    public function __construct(
        private Filesystem $filesystem,
        private string $tmpUploadDir,
    ) {
    }

    public function moveFromPhpTmp(ImageId $id, ImageFormat $format, string $phpTmpPath): string
    {
        if (!$this->filesystem->exists($this->tmpUploadDir)) {
            $this->filesystem->mkdir($this->tmpUploadDir);
        }

        $targetPath = $this->tmpUploadDir . '/' . (string) $id . '.' . $format->extension();

        $this->filesystem->copy($phpTmpPath, $targetPath, true);

        return $targetPath;
    }
}
