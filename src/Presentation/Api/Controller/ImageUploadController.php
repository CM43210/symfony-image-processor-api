<?php

declare(strict_types=1);

namespace App\Presentation\Api\Controller;

use App\Core\Image\Application\UploadImage;
use App\Presentation\Api\Dto\UploadImageRequest;
use App\Presentation\Api\Http\ApiResponder;
use App\Presentation\Api\Http\DtoValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ImageUploadController extends AbstractController
{
    public function __construct(
        private UploadImage $uploadImage,
        private DtoValidator $validator,
        private ApiResponder $responder
    ) {
    }

    #[Route('/images', name: 'images_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $dto = UploadImageRequest::fromArray($request->files->all());
        $this->validator->validateOrThrow($dto);

        $imageId = ($this->uploadImage)(
            tmpPath: $dto->image->getPathname(),
            originalName: $dto->image->getClientOriginalName(),
            mimeType: $dto->image->getMimeType(),
            sizeInBytes: $dto->image->getSize(),
        );

        return $this->responder->accepted([
            'imageId' => $imageId,
            'message' => 'Image upload accepted for processing',
        ]);
    }
}
