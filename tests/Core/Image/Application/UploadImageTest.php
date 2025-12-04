<?php

declare(strict_types=1);

namespace App\Tests\Core\Image\Application;

use App\Core\Image\Application\Command\UploadImageCommand;
use App\Core\Image\Application\UploadImage;
use App\Core\Image\Domain\ImageFormat;
use App\Tests\Doubles\InMemoryTempImageStorage;
use App\Tests\Doubles\SpyAsyncCommandBus;
use PHPUnit\Framework\TestCase;

final class UploadImageTest extends TestCase
{
    private SpyAsyncCommandBus $commandBus;
    private InMemoryTempImageStorage $tempStorage;
    private UploadImage $useCase;

    protected function setUp(): void
    {
        $this->commandBus = new SpyAsyncCommandBus();
        $this->tempStorage = new InMemoryTempImageStorage();
        $this->useCase = new UploadImage($this->commandBus, $this->tempStorage);
    }

    public function test_moves_file_from_php_tmp_to_persistent_storage(): void
    {
        $phpTmpPath = '/tmp/phpABC123';

        ($this->useCase)(
            tmpPath: $phpTmpPath,
            originalName: 'test.jpg',
            mimeType: 'image/jpeg',
            sizeInBytes: 1024,
        );

        $persistentPath = $this->tempStorage->getPersistedPath($phpTmpPath);
        $this->assertNotNull($persistentPath);
        $this->assertStringContainsString('var/tmp/uploads/', $persistentPath);
    }

    public function test_dispatches_upload_image_command(): void
    {
        ($this->useCase)(
            tmpPath: '/tmp/phpABC123',
            originalName: 'test.jpg',
            mimeType: 'image/jpeg',
            sizeInBytes: 2048,
        );

        $this->assertSame(1, $this->commandBus->count());
        
        $command = $this->commandBus->getLastDispatchedCommand();
        $this->assertInstanceOf(UploadImageCommand::class, $command);
    }

    public function test_command_contains_correct_data(): void
    {
        $imageId = ($this->useCase)(
            tmpPath: '/tmp/phpABC123',
            originalName: 'vacation.png',
            mimeType: 'image/png',
            sizeInBytes: 4096,
        );

        $command = $this->commandBus->getLastDispatchedCommand();

        $this->assertSame($imageId, $command->imageId);
        $this->assertSame('vacation.png', $command->originalName);
        $this->assertSame(ImageFormat::PNG, $command->format);
        $this->assertSame(4096, $command->sizeInBytes);
        $this->assertStringContainsString('var/tmp/uploads/', $command->tmpPath);
    }

    public function test_returns_image_id_as_string(): void
    {
        $imageId = ($this->useCase)(
            tmpPath: '/tmp/phpABC123',
            originalName: 'test.jpg',
            mimeType: 'image/jpeg',
            sizeInBytes: 1024,
        );

        $this->assertIsString($imageId);
    }

    public function test_different_invocations_generate_different_ids(): void
    {
        $id1 = ($this->useCase)(
            tmpPath: '/tmp/phpABC123',
            originalName: 'test1.jpg',
            mimeType: 'image/jpeg',
            sizeInBytes: 1024,
        );

        $id2 = ($this->useCase)(
            tmpPath: '/tmp/phpDEF456',
            originalName: 'test2.jpg',
            mimeType: 'image/jpeg',
            sizeInBytes: 2048,
        );

        $this->assertNotSame($id1, $id2);
        $this->assertSame(2, $this->commandBus->count());
    }
}
