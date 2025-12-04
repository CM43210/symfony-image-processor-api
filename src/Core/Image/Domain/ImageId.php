<?php

declare(strict_types=1);

namespace App\Core\Image\Domain;

use Symfony\Component\Uid\Uuid;

final readonly class ImageId
{
    private function __construct(
        private Uuid $value
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::v7());
    }

    public static function fromString(string $id): self
    {
        return new self(Uuid::fromString($id));
    }

    public function toUuid(): Uuid
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value->toRfc4122();
    }
}
