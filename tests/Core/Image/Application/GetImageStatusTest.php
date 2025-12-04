<?php

declare(strict_types=1);

namespace App\Tests\Core\Image\Application;

use App\Core\Image\Application\GetImageStatus;
use App\Core\Image\Domain\ImageId;
use App\Core\Image\Domain\ProcessingStatus;
use App\Tests\Doubles\InMemoryImageProcessingTracker;
use PHPUnit\Framework\TestCase;

final class GetImageStatusTest extends TestCase
{
    private InMemoryImageProcessingTracker $tracker;
    private GetImageStatus $useCase;

    protected function setUp(): void
    {
        $this->tracker = new InMemoryImageProcessingTracker();
        $this->useCase = new GetImageStatus($this->tracker);
    }

    public function test_returns_null_when_image_not_found(): void
    {
        $imageId = ImageId::generate();

        $result = ($this->useCase)($imageId);

        $this->assertNull($result);
    }

    public function test_returns_processing_status_after_start(): void
    {
        $imageId = ImageId::generate();
        $this->tracker->start($imageId);

        $result = ($this->useCase)($imageId);

        $this->assertNotNull($result);
        $this->assertSame(ProcessingStatus::PROCESSING, $result->status);
        $this->assertSame(0, $result->progress);
        $this->assertSame('Processing started', $result->message);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->startedAt);
        $this->assertNull($result->finishedAt);
    }

    public function test_returns_updated_progress(): void
    {
        $imageId = ImageId::generate();
        $this->tracker->start($imageId);
        $this->tracker->updateProgress($imageId, 50, 'Generating medium variant');

        $result = ($this->useCase)($imageId);

        $this->assertNotNull($result);
        $this->assertSame(ProcessingStatus::PROCESSING, $result->status);
        $this->assertSame(50, $result->progress);
        $this->assertSame('Generating medium variant', $result->message);
    }

    public function test_returns_completed_status(): void
    {
        $imageId = ImageId::generate();
        $this->tracker->start($imageId);
        $this->tracker->complete($imageId);

        $result = ($this->useCase)($imageId);

        $this->assertNotNull($result);
        $this->assertSame(ProcessingStatus::COMPLETED, $result->status);
        $this->assertSame(100, $result->progress);
        $this->assertNull($result->message);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->finishedAt);
    }

    public function test_returns_failed_status_with_error_message(): void
    {
        $imageId = ImageId::generate();
        $this->tracker->start($imageId);
        $this->tracker->fail($imageId, 'File not found');

        $result = ($this->useCase)($imageId);

        $this->assertNotNull($result);
        $this->assertSame(ProcessingStatus::FAILED, $result->status);
        $this->assertSame('File not found', $result->message);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result->finishedAt);
    }
}
