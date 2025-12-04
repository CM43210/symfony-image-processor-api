<?php

declare(strict_types=1);

namespace App\Presentation\Api\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponder
{
    public function success(array|object $data, int $status = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    public function created(array|object $data): JsonResponse
    {
        return $this->success($data, Response::HTTP_CREATED);
    }

    public function accepted(array|object $data): JsonResponse
    {
        return $this->success($data, Response::HTTP_ACCEPTED);
    }

    public function error(string $title, int $status, ?string $detail = null, array $extra = []): JsonResponse
    {
        $body = array_merge([
            'type' => 'about:blank',
            'title' => $title,
            'status' => $status,
        ], $detail ? ['detail' => $detail] : [], $extra);

        $res = new JsonResponse($body, $status);
        $res->headers->set('Content-Type', 'application/problem+json');
        return $res;
    }
}
