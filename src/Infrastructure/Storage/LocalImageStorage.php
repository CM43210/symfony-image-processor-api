<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Core\Image\Application\Port\ImageStorage;
use App\Core\Image\Domain\Image;
use App\Core\Image\Domain\ImageFile;
use Symfony\Component\Filesystem\Filesystem;

final class LocalImageStorage implements ImageStorage
{
    private string $storageDir;
    private Filesystem $filesystem;

    public function __construct(string $projectDir)
    {
        $this->storageDir = $projectDir . '/var/storage/images';
        $this->filesystem = new Filesystem();
        
        if (!$this->filesystem->exists($this->storageDir)) {
            $this->filesystem->mkdir($this->storageDir);
        }
    }

    public function store(ImageFile $imageFile, string $originalName): string
    {
        $extension = $imageFile->format()->extension();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        $finalFilename = $safeFilename . '.' . $extension;
        
        $destinationPath = $this->storageDir . '/' . $finalFilename;
        
        $counter = 1;
        while ($this->filesystem->exists($destinationPath)) {
            $finalFilename = $safeFilename . '_' . $counter . '.' . $extension;
            $destinationPath = $this->storageDir . '/' . $finalFilename;
            $counter++;
        }

        try {
            $this->filesystem->copy($imageFile->path(), $destinationPath);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to store image file: ' . $e->getMessage(), 0, $e);
        }

        return $finalFilename;
    }

    public function retrieve(Image $image): string
    {
        $imagePath = $this->getImagePath($image);
        
        if (!$this->filesystem->exists($imagePath)) {
            throw new \RuntimeException('Image file not found: ' . $imagePath);
        }

        return $imagePath;
    }

    public function delete(Image $image): void
    {
        $imagePath = $this->getImagePath($image);
        
        if ($this->filesystem->exists($imagePath)) {
            $this->filesystem->remove($imagePath);
        }
    }

    private function getImagePath(Image $image): string
    {
        $extension = $image->originalFile()->format()->extension();
        return $this->storageDir . '/' . (string) $image->id() . '.' . $extension;
    }
}
