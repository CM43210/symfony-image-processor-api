<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Handler;

use App\Core\Image\Application\Command\ProcessImageCommand;
use App\Core\Image\Application\Port\ImageRepositoryInterface;
use App\Core\Image\Domain\ImageId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ProcessImageHandler
{
    public function __construct(
        private ImageRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ProcessImageCommand $command): void
    {
        $imageId = ImageId::fromString($command->imageId);
        
        $image = $this->repository->findById($imageId);
        
        if ($image === null) {
            throw new \RuntimeException("Image with ID {$command->imageId} not found");
        }
    }
}
