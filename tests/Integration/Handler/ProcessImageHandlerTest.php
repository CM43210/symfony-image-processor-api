<?php

declare(strict_types=1);

namespace App\Tests\Integration\Handler;

use App\Core\Image\Application\Command\ProcessImageCommand;
use App\Core\Image\Application\Handler\ProcessImageHandler;
use App\Core\Image\Application\Port\ImageProcessingTracker;
use App\Core\Image\Application\Port\ImageRepository;
use App\Core\Image\Domain\Image;
use App\Core\Image\Domain\ImageFile;
use App\Core\Image\Domain\ImageFormat;
use App\Core\Image\Domain\ImageId;
use App\Tests\TestImageData;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProcessImageHandlerTest extends KernelTestCase
{
    private ProcessImageHandler $handler;
    private ImageRepository $repository;
    private ImageProcessingTracker $tracker;
    private string $storageDir;
    private string $archiveDir;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->handler = $container->get(ProcessImageHandler::class);
        $this->repository = $container->get(ImageRepository::class);
        $this->tracker = $container->get(ImageProcessingTracker::class);
        
        $this->storageDir = $container->getParameter('image.storage_dir');
        $this->archiveDir = $container->getParameter('archive.storage_dir');
    }

    public function test_processes_image_and_creates_archive(): void
    {
        $imageId = ImageId::generate();
        $testImagePath = $this->createTestImageInStorage($imageId);
        
        $imageFile = ImageFile::create(
            basename($testImagePath),
            ImageFormat::JPEG,
            filesize($testImagePath)
        );
        
        $image = Image::upload($imageId, $imageFile);
        $this->repository->save($image);

        $command = new ProcessImageCommand((string) $imageId);
        ($this->handler)($command);

        $processedImage = $this->repository->findById($imageId);
        $this->assertNotNull($processedImage);
        $this->assertNotNull($processedImage->processedArchivePath());
        $this->assertStringEndsWith('.zip', $processedImage->processedArchivePath());

        $archivePath = $this->archiveDir . '/' . $processedImage->processedArchivePath();
        $this->assertFileExists($archivePath);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($archivePath));
        $this->assertGreaterThanOrEqual(4, $zip->numFiles, 'Archive should contain at least 4 files');
        
        $filesInZip = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filesInZip[] = $zip->getNameIndex($i);
        }
        
        $this->assertContains('thumbnail.webp', $filesInZip);
        $this->assertContains('medium.webp', $filesInZip);
        $this->assertContains('large.webp', $filesInZip);
        $zip->close();

        $this->cleanupTestFiles($testImagePath, $archivePath);
    }

    public function test_updates_processing_status_during_processing(): void
    {
        $imageId = ImageId::generate();
        $testImagePath = $this->createTestImageInStorage($imageId);
        
        $imageFile = ImageFile::create(
            basename($testImagePath),
            ImageFormat::JPEG,
            filesize($testImagePath)
        );
        
        $image = Image::upload($imageId, $imageFile);
        $this->repository->save($image);

        $command = new ProcessImageCommand((string) $imageId);
        ($this->handler)($command);

        $status = $this->tracker->get($imageId);
        $this->assertNotNull($status);
        $this->assertSame(100, $status->progress);

        $processedImage = $this->repository->findById($imageId);
        $archivePath = $this->archiveDir . '/' . $processedImage->processedArchivePath();
        $this->cleanupTestFiles($testImagePath, $archivePath);
    }

    public function test_throws_exception_when_image_not_found(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not found');

        $nonExistentId = ImageId::generate();
        $command = new ProcessImageCommand((string) $nonExistentId);
        
        ($this->handler)($command);
    }

    private function createTestImageInStorage(ImageId $imageId): string
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }

        $filename = (string) $imageId . '.jpg';
        $path = $this->storageDir . '/' . $filename;
        
        $imageData = base64_decode(TestImageData::MINIMAL_VALID_JPEG);
        file_put_contents($path, $imageData);
        
        return $path;
    }

    private function cleanupTestFiles(string $imagePath, string $archivePath): void
    {
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        if (file_exists($archivePath)) {
            unlink($archivePath);
        }
    }
}
