<?php

declare(strict_types=1);

namespace App\Core\Image\Domain;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'image')]
final class Image
{
    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'image_id')]
        private ImageId $id,
        #[ORM\Embedded(class: ImageFile::class, columnPrefix: false)]
        private ImageFile $originalFile,
        #[ORM\Column(type: 'datetime_immutable', name: 'uploaded_at')]
        private \DateTimeImmutable $uploadedAt,
        #[ORM\Column(type: 'string', nullable: true, name: 'processed_archive_path')]
        private ?string $processedArchivePath = null,
    ) {
    }

    public static function upload(ImageId $id, ImageFile $file): self
    {
        return new self(
            $id,
            $file,
            new \DateTimeImmutable(),
        );
    }

    public function id(): ImageId
    {
        return $this->id;
    }

    public function originalFile(): ImageFile
    {
        return $this->originalFile;
    }

    public function uploadedAt(): \DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function processedArchivePath(): ?string
    {
        return $this->processedArchivePath;
    }

    public function setProcessedArchive(string $archivePath): void
    {
        $this->processedArchivePath = $archivePath;
    }
}
