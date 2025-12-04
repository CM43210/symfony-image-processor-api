<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Core\Shared\Application\Port\AsyncCommandBus;

final class SpyAsyncCommandBus implements AsyncCommandBus
{
    private array $dispatchedCommands = [];

    public function dispatch(object $command): void
    {
        $this->dispatchedCommands[] = $command;
    }

    public function getDispatchedCommands(): array
    {
        return $this->dispatchedCommands;
    }

    public function getLastDispatchedCommand(): ?object
    {
        return end($this->dispatchedCommands) ?: null;
    }

    public function count(): int
    {
        return count($this->dispatchedCommands);
    }

    public function clear(): void
    {
        $this->dispatchedCommands = [];
    }
}
