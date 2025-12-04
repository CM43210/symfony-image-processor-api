<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Core\Image\Application\Port\ImageRepository;
use App\Core\Image\Domain\Image;
use App\Core\Image\Domain\ImageId;

final class InMemoryImageRepository implements ImageRepository
{
    private array $images = [];

    public function save(Image $image): void
    {
        $this->images[(string) $image->id()] = $image;
    }

    public function findById(ImageId $id): ?Image
    {
        return $this->images[(string) $id] ?? null;
    }

    public function clear(): void
    {
        $this->images = [];
    }

    public function count(): int
    {
        return count($this->images);
    }
}
