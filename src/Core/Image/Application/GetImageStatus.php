<?php

declare(strict_types=1);

namespace App\Core\Image\Application;

use App\Core\Image\Application\Port\ImageProcessingTracker;
use App\Core\Image\Application\View\ImageProcessingStatusView;
use App\Core\Image\Domain\ImageId;

final readonly class GetImageStatus
{
    public function __construct(
        private ImageProcessingTracker $tracker,
    ) {
    }

    public function __invoke(ImageId $id): ?ImageProcessingStatusView
    {
        return $this->tracker->get($id);
    }
}
