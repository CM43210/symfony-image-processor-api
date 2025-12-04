<?php

declare(strict_types=1);

namespace App\Presentation\Api\Controller;

use App\Core\Image\Application\Query\DownloadProcessedImage;
use App\Core\Image\Domain\ImageId;
use App\Presentation\Api\Http\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ImageDownloadController extends AbstractController
{
    public function __construct(
        private readonly DownloadProcessedImage $downloadProcessedImage,
        private readonly ApiResponder $responder,
    ) {
    }

    #[Route('/images/{id}/download', name: 'image_download', methods: ['GET'])]
    public function download(string $id): Response
    {
        try {
            $imageId = ImageId::fromString($id);
        } catch (\Throwable) {
            return $this->responder->error('Invalid image ID', status: 400);
        }

        $downloadView = ($this->downloadProcessedImage)($imageId);

        if ($downloadView === null) {
            return $this->responder->error(
                'Image not found or processing not completed yet',
                status: 404
            );
        }

        return new BinaryFileResponse(
            file: $downloadView->absolutePath,
            status: 200,
            headers: [
                'Content-Type' => $downloadView->contentType,
                'Content-Disposition' => 'attachment; filename="' . $downloadView->filename . '"',
            ],
            public: false,
            autoEtag: true,
            autoLastModified: true
        );
    }
}
