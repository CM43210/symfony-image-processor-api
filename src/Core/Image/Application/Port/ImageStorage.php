<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Port;

use App\Core\Image\Domain\Image;
use App\Core\Image\Domain\ImageFile;

interface ImageStorage
{
    public function store(ImageFile $imageFile, string $originalName): string;

    public function retrieve(Image $image): string;

    public function delete(Image $image): void;
}
