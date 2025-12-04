<?php

declare(strict_types=1);

namespace App\Core\Image\Domain;

final class Image
{
    private function __construct(
        private ImageId $id,
        private ImageFile $originalFile,
        private \DateTimeImmutable $uploadedAt,
    ) {
    }

    public static function upload(ImageId $id, ImageFile $file): self
    {
        return new self(
            $id,
            $file,
            new \DateTimeImmutable(),
        );
    }

    public function id(): ImageId
    {
        return $this->id;
    }

    public function originalFile(): ImageFile
    {
        return $this->originalFile;
    }

    public function uploadedAt(): \DateTimeImmutable
    {
        return $this->uploadedAt;
    }
}
