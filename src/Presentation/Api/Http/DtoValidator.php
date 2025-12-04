<?php

declare(strict_types=1);

namespace App\Presentation\Api\Http;

use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DtoValidator
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function validateOrThrow(object $dto): void
    {
        $violations = $this->validator->validate($dto);
        if (\count($violations) === 0) {
            return;
        }
        $errors = [];
        foreach ($violations as $v) {
            $errors[] = $v->getPropertyPath() . ': ' . $v->getMessage();
        }
        throw new ValidationException($errors);
    }
}
