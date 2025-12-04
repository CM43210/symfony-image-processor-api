<?php

declare(strict_types=1);

namespace App\Core\Image\Domain;

enum ImageFormat: string
{
    case JPEG = 'jpeg';
    case PNG = 'png';

    public static function fromMimeType(string $mimeType): self
    {
        return match ($mimeType) {
            'image/jpeg' => self::JPEG,
            'image/png' => self::PNG,
            default => throw new \InvalidArgumentException("Unsupported mime type: {$mimeType}"),
        };
    }

    public function toMimeType(): string
    {
        return match ($this) {
            self::JPEG => 'image/jpeg',
            self::PNG => 'image/png',
        };
    }

    public function extension(): string
    {
        return $this->value;
    }
}
