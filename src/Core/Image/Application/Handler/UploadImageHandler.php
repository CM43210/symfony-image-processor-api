<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Handler;

use App\Core\Image\Application\Command\UploadImageCommand;
use App\Core\Image\Application\Port\ImageRepositoryInterface;
use App\Core\Image\Application\Port\ImageStorageInterface;
use App\Core\Image\Domain\Image;
use App\Core\Image\Domain\ImageFile;
use App\Core\Image\Domain\ImageId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UploadImageHandler
{
    public function __construct(
        private ImageStorageInterface $storage,
        private ImageRepositoryInterface $repository,
    ) {
    }

    public function __invoke(UploadImageCommand $command): Image
    {
        $imageId = ImageId::fromString($command->imageId);
        
        $imageFile = ImageFile::create(
            $command->tmpPath,
            $command->format,
            $command->sizeInBytes
        );
        
        $storedPath = $this->storage->store($imageFile, $command->originalName);
        
        $finalImageFile = ImageFile::create(
            $storedPath,
            $command->format,
            $command->sizeInBytes
        );

        $image = Image::upload($imageId, $finalImageFile);

        $this->repository->save($image);

        return $image;
    }
}
