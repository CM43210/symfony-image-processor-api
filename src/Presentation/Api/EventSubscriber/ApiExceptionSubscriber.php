<?php

declare(strict_types=1);

namespace App\Presentation\Api\EventSubscriber;

use App\Presentation\Api\Http\ApiResponder;
use App\Presentation\Api\Http\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private ApiResponder $responder)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }

    public function onException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if ($e instanceof ValidationException) {
            $event->setResponse(
                $this->responder->error(
                    'Validation failed',
                    400,
                    'One or more fields have invalid values.',
                    ['errors' => $e->errors]
                )
            );
            return;
        }

        if ($e instanceof \DomainException) {
            $event->setResponse(
                $this->responder->error(
                    'Domain rule violation',
                    400,
                    $e->getMessage()
                )
            );
            return;
        }
    }
}
