<?php

declare(strict_types=1);

namespace App\Presentation\Api\Controller;

use App\Core\Image\Application\GetImageStatus;
use App\Core\Image\Domain\ImageId;
use App\Presentation\Api\Http\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ImageStatusController extends AbstractController
{
    public function __construct(
        private readonly GetImageStatus $getImageStatus,
        private readonly ApiResponder $responder,
    ) {
    }

    #[Route('/images/{id}/status', name: 'image_status', methods: ['GET'])]
    public function status(string $id): JsonResponse
    {
        try {
            $imageId = ImageId::fromString($id);
        } catch (\Throwable) {
            return $this->responder->error('Invalid image ID', status: 400);
        }

        $statusView = ($this->getImageStatus)($imageId);

        if ($statusView === null) {
            return $this->responder->error('Image processing status not found', status: 404);
        }

        return $this->responder->success([
            'imageId' => $id,
            'status' => $statusView->status->value,
            'progress' => $statusView->progress,
            'message' => $statusView->message,
            'startedAt' => $statusView->startedAt?->format(\DATE_ATOM),
            'finishedAt' => $statusView->finishedAt?->format(\DATE_ATOM),
        ]);
    }
}
