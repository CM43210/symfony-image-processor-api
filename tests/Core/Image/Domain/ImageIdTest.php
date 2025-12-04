<?php

declare(strict_types=1);

namespace App\Tests\Core\Image\Domain;

use App\Core\Image\Domain\ImageId;
use PHPUnit\Framework\TestCase;

final class ImageIdTest extends TestCase
{
    public function test_generates_valid_uuid_v7(): void
    {
        $imageId = ImageId::generate();

        $this->assertInstanceOf(ImageId::class, $imageId);
        $this->assertNotEmpty((string) $imageId);
    }

    public function test_creates_from_valid_uuid_string(): void
    {
        $uuidString = '01938f21-2a4e-7000-8000-000000000000';
        $imageId = ImageId::fromString($uuidString);

        $this->assertInstanceOf(ImageId::class, $imageId);
        $this->assertSame($uuidString, (string) $imageId);
    }

    public function test_throws_exception_for_invalid_uuid_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ImageId::fromString('invalid-uuid');
    }

    public function test_two_generated_ids_are_different(): void
    {
        $id1 = ImageId::generate();
        $id2 = ImageId::generate();

        $this->assertNotSame((string) $id1, (string) $id2);
    }
}
