<?php

declare(strict_types=1);

namespace App\Tests\Core\Image\Domain;

use App\Core\Image\Domain\Image;
use App\Core\Image\Domain\ImageFile;
use App\Core\Image\Domain\ImageFormat;
use App\Core\Image\Domain\ImageId;
use PHPUnit\Framework\TestCase;

final class ImageTest extends TestCase
{
    public function test_uploads_new_image(): void
    {
        $imageId = ImageId::generate();
        $imageFile = ImageFile::create('test.jpg', ImageFormat::JPEG, 1024);

        $image = Image::upload($imageId, $imageFile);

        $this->assertSame($imageId, $image->id());
        $this->assertSame($imageFile, $image->originalFile());
        $this->assertInstanceOf(\DateTimeImmutable::class, $image->uploadedAt());
        $this->assertNull($image->processedArchivePath());
    }

    public function test_uploaded_at_is_current_time(): void
    {
        $before = new \DateTimeImmutable();
        
        $image = Image::upload(
            ImageId::generate(),
            ImageFile::create('test.jpg', ImageFormat::JPEG, 1024)
        );
        
        $after = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before->getTimestamp(), $image->uploadedAt()->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $image->uploadedAt()->getTimestamp());
    }

    public function test_sets_processed_archive_path(): void
    {
        $image = Image::upload(
            ImageId::generate(),
            ImageFile::create('test.jpg', ImageFormat::JPEG, 1024)
        );

        $this->assertNull($image->processedArchivePath());

        $archivePath = 'archives/image-123.zip';
        $image->setProcessedArchive($archivePath);

        $this->assertSame($archivePath, $image->processedArchivePath());
    }

    public function test_can_overwrite_processed_archive_path(): void
    {
        $image = Image::upload(
            ImageId::generate(),
            ImageFile::create('test.jpg', ImageFormat::JPEG, 1024)
        );

        $image->setProcessedArchive('old-path.zip');
        $image->setProcessedArchive('new-path.zip');

        $this->assertSame('new-path.zip', $image->processedArchivePath());
    }
}
