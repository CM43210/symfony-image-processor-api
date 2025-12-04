<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Command;

final readonly class ProcessImageCommand
{
    public function __construct(
        public string $imageId,
    ) {
    }
}
