<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Port;

use App\Core\Image\Application\View\ImageProcessingStatusView;
use App\Core\Image\Domain\ImageId;

interface ImageProcessingTracker
{
    public function start(ImageId $id): void;
    public function updateProgress(ImageId $id, int $progress, ?string $message = null): void;
    public function complete(ImageId $id): void;
    public function fail(ImageId $id, string $error): void;
    public function get(ImageId $id): ?ImageProcessingStatusView;
}
