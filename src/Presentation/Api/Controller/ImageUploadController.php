<?php

declare(strict_types=1);

namespace App\Presentation\Api\Controller;

use App\Presentation\Api\Http\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ImageUploadController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly ApiResponder $responder
    ) {
    }

    #[Route('/images', name: 'images_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        return $this->responder->accepted([
            'message' => '',
        ]);
    }
}
