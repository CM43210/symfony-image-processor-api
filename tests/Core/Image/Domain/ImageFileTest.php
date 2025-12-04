<?php

declare(strict_types=1);

namespace App\Tests\Core\Image\Domain;

use App\Core\Image\Domain\ImageFile;
use App\Core\Image\Domain\ImageFormat;
use PHPUnit\Framework\TestCase;

final class ImageFileTest extends TestCase
{
    public function test_creates_valid_image_file(): void
    {
        $file = ImageFile::create(
            'uploads/test.jpg',
            ImageFormat::JPEG,
            1024
        );

        $this->assertSame('uploads/test.jpg', $file->path());
        $this->assertSame(ImageFormat::JPEG, $file->format());
        $this->assertSame(1024, $file->sizeInBytes());
    }

    public function test_throws_exception_for_zero_size(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('File size must be positive');

        ImageFile::create('test.jpg', ImageFormat::JPEG, 0);
    }

    public function test_throws_exception_for_negative_size(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('File size must be positive');

        ImageFile::create('test.jpg', ImageFormat::JPEG, -100);
    }

    public function test_throws_exception_when_exceeds_max_size(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('File size exceeds maximum allowed');

        $tooLarge = (20 * 1024 * 1024) + 1;
        ImageFile::create('test.jpg', ImageFormat::JPEG, $tooLarge);
    }

    public function test_accepts_max_allowed_size(): void
    {
        $maxSize = 20 * 1024 * 1024;
        $file = ImageFile::create('test.jpg', ImageFormat::JPEG, $maxSize);

        $this->assertSame($maxSize, $file->sizeInBytes());
    }

    public function test_supports_png_format(): void
    {
        $file = ImageFile::create('test.png', ImageFormat::PNG, 2048);

        $this->assertSame(ImageFormat::PNG, $file->format());
    }
}
