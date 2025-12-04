<?php

declare(strict_types=1);

namespace App\Core\Image\Domain;

final readonly class ImageFile
{
    private const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB

    private function __construct(
        private string $path,
        private ImageFormat $format,
        private int $sizeInBytes,
    ) {
    }

    public static function create(string $path, ImageFormat $format, int $sizeInBytes): self
    {
        if ($sizeInBytes <= 0) {
            throw new \DomainException("File size must be positive");
        }

        if ($sizeInBytes > self::MAX_FILE_SIZE) {
            throw new \DomainException(
                sprintf("File size exceeds maximum allowed: %d bytes", self::MAX_FILE_SIZE)
            );
        }

        return new self($path, $format, $sizeInBytes);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function format(): ImageFormat
    {
        return $this->format;
    }

    public function sizeInBytes(): int
    {
        return $this->sizeInBytes;
    }
}
