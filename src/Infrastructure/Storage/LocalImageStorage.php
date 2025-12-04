<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Core\Image\Application\Port\ImageStorageInterface;
use App\Core\Image\Domain\Image;
use App\Core\Image\Domain\ImageFile;

final class LocalImageStorage implements ImageStorageInterface
{
    public function store(ImageFile $imageFile, string $originalName): string
    {
    }

    public function retrieve(Image $image): string
    {
    }

    public function delete(Image $image): void
    {
    }
}
