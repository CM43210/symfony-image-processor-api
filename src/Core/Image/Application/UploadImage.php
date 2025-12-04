<?php

declare(strict_types=1);

namespace App\Core\Image\Application;

use App\Core\Image\Application\Command\UploadImageCommand;
use App\Core\Image\Application\Port\TempImageStorage;
use App\Core\Image\Domain\ImageFormat;
use App\Core\Image\Domain\ImageId;
use App\Core\Shared\Application\Port\AsyncCommandBus;

final readonly class UploadImage
{
    public function __construct(
        private AsyncCommandBus $commandBus,
        private TempImageStorage $tempImageStorage,
    ) {
    }

    public function __invoke(
        string $tmpPath,
        string $originalName,
        string $mimeType,
        int $sizeInBytes,
    ): string {
        $imageId = ImageId::generate();
        $format = ImageFormat::fromMimeType($mimeType);

        $persistentTmpPath = $this->tempImageStorage
            ->moveFromPhpTmp($imageId, $format, $tmpPath);

        $command = new UploadImageCommand(
            imageId: (string) $imageId,
            tmpPath: $persistentTmpPath,
            originalName: $originalName,
            format: $format,
            sizeInBytes: $sizeInBytes,
        );

        $this->commandBus->dispatch($command);

        return (string) $imageId;
    }
}
