<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Handler;

use App\Core\Image\Application\Command\ProcessImageCommand;
use App\Core\Image\Application\Command\UploadImageCommand;
use App\Core\Image\Application\Port\ImageRepository;
use App\Core\Image\Application\Port\ImageStorage;
use App\Core\Image\Domain\Image;
use App\Core\Image\Domain\ImageFile;
use App\Core\Image\Domain\ImageId;
use App\Core\Shared\Application\Port\AsyncCommandBus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UploadImageHandler
{
    public function __construct(
        private ImageStorage $storage,
        private ImageRepository $repository,
        private AsyncCommandBus $commandBus,
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
        
        $this->commandBus->dispatch(
            new ProcessImageCommand($command->imageId)
        );

        return $image;
    }
}
