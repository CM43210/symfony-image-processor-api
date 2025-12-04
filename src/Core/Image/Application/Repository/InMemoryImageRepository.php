<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Repository;

use App\Core\Image\Application\Port\ImageRepositoryInterface;
use App\Core\Image\Domain\Image;
use App\Core\Image\Domain\ImageId;

final class InMemoryImageRepository implements ImageRepositoryInterface
{
    private array $images = [];

    public function save(Image $image): void
    {
        $this->images[$image->id()->__toString()] = $image;
    }

    public function findById(ImageId $id): ?Image
    {
        return $this->images[$id->__toString()] ?? null;
    }
}
