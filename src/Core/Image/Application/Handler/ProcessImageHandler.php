<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Handler;

use App\Core\Image\Application\Command\ProcessImageCommand;
use App\Core\Image\Application\Port\ImageProcessingTracker;
use App\Core\Image\Application\Port\ImageProcessor;
use App\Core\Image\Application\Port\ImageRepositoryInterface;
use App\Core\Image\Domain\ImageId;
use App\Core\Shared\Application\Port\Logger;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ProcessImageHandler
{
    public function __construct(
        private ImageRepositoryInterface $repository,
        private ImageProcessor $processor,
        private ImageProcessingTracker $tracker,
        private Logger $logger,
        private string $storageDir,
        private string $archiveDir,
    ) {
    }

    public function __invoke(ProcessImageCommand $command): void
    {
        $this->logger->info('Starting image processing', ['imageId' => $command->imageId]);

        $imageId = ImageId::fromString($command->imageId);
        
        $this->tracker->start($imageId);

        $image = $this->repository->findById($imageId);

        if ($image === null) {
            $this->logger->error('Image not found', ['imageId' => $command->imageId]);
            $this->tracker->fail($imageId, 'Image not found');
            throw new \RuntimeException("Image with ID {$command->imageId} not found");
        }

        $originalPath = $this->storageDir . '/' . $image->originalFile()->path();

        if (!file_exists($originalPath)) {
            $this->tracker->fail($imageId, 'Original file not found');
            throw new \RuntimeException("Original file not found: {$originalPath}");
        }

        $workDir = sys_get_temp_dir() . '/image-processing/' . $command->imageId;
        if (!is_dir($workDir) && !mkdir($workDir, 0755, true) && !is_dir($workDir)) {
            $this->tracker->fail($imageId, 'Failed to create working directory');
            throw new \RuntimeException("Failed to create working directory: {$workDir}");
        }

        try {
            $this->tracker->updateProgress($imageId, 10, 'Removing metadata');
            $originalName = 'original.' . $image->originalFile()->format()->extension();
            $cleanOriginalPath = $workDir . '/' . $originalName;
            copy($originalPath, $cleanOriginalPath);
            $this->processor->removeMetadata($cleanOriginalPath);
            $this->logger->debug('Removed metadata from original', ['imageId' => $command->imageId]);

            $this->tracker->updateProgress($imageId, 30, 'Generating thumbnail');
            $thumbnailPath = $workDir . '/thumbnail.webp';
            $this->processor->resize($originalPath, $thumbnailPath, 300, 300, 80);
            $this->logger->info('Generated thumbnail variant', ['imageId' => $command->imageId]);

            $this->tracker->updateProgress($imageId, 50, 'Generating medium variant');
            $mediumPath = $workDir . '/medium.webp';
            $this->processor->resize($originalPath, $mediumPath, 800, 800, 85);
            $this->logger->info('Generated medium variant', ['imageId' => $command->imageId]);

            $this->tracker->updateProgress($imageId, 70, 'Generating large variant');
            $largePath = $workDir . '/large.webp';
            $this->processor->resize($originalPath, $largePath, 1920, 1920, 90);
            $this->logger->info('Generated large variant', ['imageId' => $command->imageId]);

            $this->tracker->updateProgress($imageId, 90, 'Creating archive');
            $archiveName = $command->imageId . '.zip';
            $archivePath = $this->archiveDir . '/' . $archiveName;

            if (!is_dir($this->archiveDir) && !mkdir($this->archiveDir, 0755, true) && !is_dir($this->archiveDir)) {
                throw new \RuntimeException("Failed to create archive directory: {$this->archiveDir}");
            }

            $zip = new \ZipArchive();
            if ($zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException("Failed to create ZIP archive: {$archivePath}");
            }

            $zip->addFile($cleanOriginalPath, $originalName);
            $zip->addFile($thumbnailPath, 'thumbnail.webp');
            $zip->addFile($mediumPath, 'medium.webp');
            $zip->addFile($largePath, 'large.webp');
            $zip->close();
            $this->logger->info('Created ZIP archive', ['imageId' => $command->imageId, 'archive' => $archiveName]);

            $image->setProcessedArchive($archiveName);
            $this->repository->save($image);
            
            $this->tracker->complete($imageId);
            $this->logger->info('Image processing completed successfully', ['imageId' => $command->imageId]);
        } catch (\Throwable $e) {
            $this->tracker->fail($imageId, $e->getMessage());
            $this->logger->error('Image processing failed', [
                'imageId' => $command->imageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        } finally {
            $this->cleanupDirectory($workDir);
        }
    }

    private function cleanupDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->cleanupDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
