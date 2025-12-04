<?php

declare(strict_types=1);

namespace App\Presentation\Api\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

final class UploadImageRequest
{
    #[Assert\NotNull(message: 'Image file is required')]
    #[Assert\File(
        maxSize: '20M',
        mimeTypes: ['image/jpeg', 'image/png'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG)',
    )]
    public ?UploadedFile $image = null;

    public static function fromArray(array $files): self
    {
        $self = new self();
        $self->image = $files['image'] ?? null;
        return $self;
    }
}
