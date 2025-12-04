<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Core\Image\Application\Port\ImageProcessingTracker;
use App\Core\Image\Application\View\ImageProcessingStatusView;
use App\Core\Image\Domain\ImageId;
use App\Core\Image\Domain\ProcessingStatus;

final class InMemoryImageProcessingTracker implements ImageProcessingTracker
{
    private array $statuses = [];

    public function start(ImageId $imageId): void
    {
        $this->statuses[(string) $imageId] = new ImageProcessingStatusView(
            status: ProcessingStatus::PROCESSING,
            progress: 0,
            message: 'Processing started',
            startedAt: new \DateTimeImmutable(),
            finishedAt: null,
        );
    }

    public function updateProgress(ImageId $imageId, int $progress, ?string $message = null): void
    {
        if (!isset($this->statuses[(string) $imageId])) {
            return;
        }

        $current = $this->statuses[(string) $imageId];
        
        $this->statuses[(string) $imageId] = new ImageProcessingStatusView(
            status: ProcessingStatus::PROCESSING,
            progress: $progress,
            message: $message,
            startedAt: $current->startedAt,
            finishedAt: null,
        );
    }

    public function complete(ImageId $imageId): void
    {
        if (!isset($this->statuses[(string) $imageId])) {
            return;
        }

        $current = $this->statuses[(string) $imageId];
        
        $this->statuses[(string) $imageId] = new ImageProcessingStatusView(
            status: ProcessingStatus::COMPLETED,
            progress: 100,
            message: null,
            startedAt: $current->startedAt,
            finishedAt: new \DateTimeImmutable(),
        );
    }

    public function fail(ImageId $imageId, string $errorMessage): void
    {
        if (!isset($this->statuses[(string) $imageId])) {
            return;
        }

        $current = $this->statuses[(string) $imageId];
        
        $this->statuses[(string) $imageId] = new ImageProcessingStatusView(
            status: ProcessingStatus::FAILED,
            progress: $current->progress,
            message: $errorMessage,
            startedAt: $current->startedAt,
            finishedAt: new \DateTimeImmutable(),
        );
    }

    public function get(ImageId $imageId): ?ImageProcessingStatusView
    {
        return $this->statuses[(string) $imageId] ?? null;
    }

    public function clear(): void
    {
        $this->statuses = [];
    }
}
