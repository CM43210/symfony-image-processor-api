<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Core\Image\Application\Port\TempImageStorage;
use App\Core\Image\Domain\ImageFormat;
use App\Core\Image\Domain\ImageId;

final class InMemoryTempImageStorage implements TempImageStorage
{
    private array $files = [];

    public function moveFromPhpTmp(ImageId $imageId, ImageFormat $format, string $phpTmpPath): string
    {
        $persistentPath = sprintf(
            'var/tmp/uploads/%s.%s',
            (string) $imageId,
            $format->extension()
        );

        $this->files[$phpTmpPath] = $persistentPath;

        return $persistentPath;
    }

    public function getPersistedPath(string $phpTmpPath): ?string
    {
        return $this->files[$phpTmpPath] ?? null;
    }

    public function clear(): void
    {
        $this->files = [];
    }
}
