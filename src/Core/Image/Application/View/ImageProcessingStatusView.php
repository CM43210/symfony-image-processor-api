<?php

declare(strict_types=1);

namespace App\Core\Image\Application\View;

use App\Core\Image\Domain\ProcessingStatus;

final readonly class ImageProcessingStatusView
{
    public function __construct(
        public ProcessingStatus $status,
        public int $progress,
        public ?string $message = null,
        public ?\DateTimeImmutable $startedAt = null,
        public ?\DateTimeImmutable $finishedAt = null,
    ) {
    }
}
