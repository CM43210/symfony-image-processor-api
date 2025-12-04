<?php

declare(strict_types=1);

namespace App\Presentation\Api\Http;

final class ValidationException extends \RuntimeException
{
    public function __construct(public array $errors)
    {
        parent::__construct('Validation failed');
    }
}
