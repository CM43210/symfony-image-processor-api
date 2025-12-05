<?php

declare(strict_types=1);

namespace App\Tests\Integration\Handler;

use App\Core\Image\Application\Command\ProcessImageCommand;
use App\Core\Image\Application\Command\UploadImageCommand;
use App\Core\Image\Application\Handler\UploadImageHandler;
use App\Core\Image\Application\Port\ImageRepository;
use App\Core\Image\Application\Port\ImageStorage;
use App\Core\Image\Domain\ImageFormat;
use App\Core\Image\Domain\ImageId;
use App\Tests\Doubles\SpyAsyncCommandBus;
use App\Tests\TestImageData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UploadImageHandlerTest extends KernelTestCase
{
    private ImageRepository $repository;
    private ImageStorage $storage;
    private SpyAsyncCommandBus $commandBus;
    private UploadImageHandler $handler;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->repository = $container->get(ImageRepository::class);
        $this->storage = $container->get(ImageStorage::class);
        $this->commandBus = new SpyAsyncCommandBus();
        
        $this->handler = new UploadImageHandler(
            $this->storage,
            $this->repository,
            $this->commandBus
        );
    }

    public function test_stores_image_in_storage_and_database(): void
    {
        $imageId = ImageId::generate();
        $tmpPath = $this->createTempTestFile();

        $command = new UploadImageCommand(
            imageId: (string) $imageId,
            tmpPath: $tmpPath,
            originalName: 'test-photo.jpg',
            format: ImageFormat::JPEG,
            sizeInBytes: 1024,
        );

        $image = ($this->handler)($command);

        $this->assertNotNull($image);
        $this->assertSame((string) $imageId, (string) $image->id());

        $retrievedImage = $this->repository->findById($imageId);
        $this->assertNotNull($retrievedImage);
        $this->assertSame((string) $imageId, (string) $retrievedImage->id());
        $this->assertSame(ImageFormat::JPEG, $retrievedImage->originalFile()->format());
        $this->assertSame(1024, $retrievedImage->originalFile()->sizeInBytes());

        $this->cleanupTempFile($tmpPath);
    }

    public function test_dispatches_process_image_command(): void
    {
        $imageId = ImageId::generate();
        $tmpPath = $this->createTempTestFile();

        $command = new UploadImageCommand(
            imageId: (string) $imageId,
            tmpPath: $tmpPath,
            originalName: 'test-photo.jpg',
            format: ImageFormat::JPEG,
            sizeInBytes: 1024,
        );

        ($this->handler)($command);

        $this->assertSame(1, $this->commandBus->count());
        
        $dispatchedCommand = $this->commandBus->getLastDispatchedCommand();
        $this->assertInstanceOf(ProcessImageCommand::class, $dispatchedCommand);
        $this->assertSame((string) $imageId, $dispatchedCommand->imageId);

        $this->cleanupTempFile($tmpPath);
    }

    public function test_stored_file_has_different_path_than_tmp(): void
    {
        $imageId = ImageId::generate();
        $tmpPath = $this->createTempTestFile();

        $command = new UploadImageCommand(
            imageId: (string) $imageId,
            tmpPath: $tmpPath,
            originalName: 'photo.jpg',
            format: ImageFormat::JPEG,
            sizeInBytes: 2048,
        );

        $image = ($this->handler)($command);

        $this->assertNotSame($tmpPath, $image->originalFile()->path());
        $this->assertStringNotContainsString('tmp', $image->originalFile()->path());

        $this->cleanupTempFile($tmpPath);
    }

    private function createTempTestFile(): string
    {
        $tmpPath = sys_get_temp_dir() . '/test_image_' . uniqid() . '.jpg';
        
        $imageData = base64_decode(TestImageData::MINIMAL_VALID_JPEG);
        file_put_contents($tmpPath, $imageData);
        
        return $tmpPath;
    }

    private function cleanupTempFile(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
