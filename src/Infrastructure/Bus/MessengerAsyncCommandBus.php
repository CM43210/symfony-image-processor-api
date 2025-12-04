<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

use App\Core\Shared\Application\Port\AsyncCommandBus;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerAsyncCommandBus implements AsyncCommandBus
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
    }

    public function dispatch(object $command): void
    {
        $this->bus->dispatch($command);
    }
}
