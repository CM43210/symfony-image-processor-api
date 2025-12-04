<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use App\Core\Shared\Application\Port\Logger;
use Psr\Log\LoggerInterface;

final readonly class MonologAdapter implements Logger
{
    public function __construct(
        private LoggerInterface $monolog,
    ) {
    }

    public function info(string $message, array $context = []): void
    {
        $this->monolog->info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->monolog->error($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->monolog->debug($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->monolog->warning($message, $context);
    }
}
