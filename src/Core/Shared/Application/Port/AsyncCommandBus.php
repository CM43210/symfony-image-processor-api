<?php

declare(strict_types=1);

namespace App\Core\Shared\Application\Port;

interface AsyncCommandBus
{
    public function dispatch(object $command): void;
}
