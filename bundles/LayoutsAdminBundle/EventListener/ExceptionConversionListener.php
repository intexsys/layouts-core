<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\EventListener;

use Exception;
use InvalidArgumentException as BaseInvalidArgumentException;
use Netgen\Layouts\Exception\BadStateException;
use Netgen\Layouts\Exception\InvalidArgumentException;
use Netgen\Layouts\Exception\NotFoundException;
use Netgen\Layouts\Exception\Validation\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class ExceptionConversionListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $exceptionMap = [
        NotFoundException::class => NotFoundHttpException::class,
        InvalidArgumentException::class => BadRequestHttpException::class,
        ValidationException::class => BadRequestHttpException::class,
        BadStateException::class => UnprocessableEntityHttpException::class,
        // Various other useful exceptions
        AccessDeniedException::class => AccessDeniedHttpException::class,
        BaseInvalidArgumentException::class => BadRequestHttpException::class,
    ];

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onException', 10]];
    }

    /**
     * Converts exceptions to Symfony HTTP exceptions.
     */
    public function onException(GetResponseForExceptionEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $attributes = $event->getRequest()->attributes;
        if ($attributes->get(SetIsApiRequestListener::API_FLAG_NAME) !== true) {
            return;
        }

        $exception = $event->getException();
        if ($exception instanceof HttpExceptionInterface) {
            return;
        }

        $exceptionClass = null;
        foreach ($this->exceptionMap as $sourceException => $targetException) {
            if (is_a($exception, $sourceException, true)) {
                $exceptionClass = $targetException;

                break;
            }
        }

        if ($exceptionClass !== null) {
            $convertedException = new $exceptionClass(
                $exception->getMessage(),
                $exception,
                $exception->getCode()
            );

            if ($convertedException instanceof Exception) {
                $event->setException($convertedException);
            }
        }
    }
}
