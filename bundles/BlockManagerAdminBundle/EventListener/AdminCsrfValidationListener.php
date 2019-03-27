<?php

declare(strict_types=1);

namespace Netgen\Bundle\BlockManagerAdminBundle\EventListener;

use Netgen\Bundle\BlockManagerBundle\Security\CsrfTokenValidatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class AdminCsrfValidationListener implements EventSubscriberInterface
{
    /**
     * @var \Netgen\Bundle\BlockManagerBundle\Security\CsrfTokenValidatorInterface
     */
    private $csrfTokenValidator;

    /**
     * @var string
     */
    private $csrfTokenId;

    public function __construct(CsrfTokenValidatorInterface $csrfTokenValidator, string $csrfTokenId)
    {
        $this->csrfTokenValidator = $csrfTokenValidator;
        $this->csrfTokenId = $csrfTokenId;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    /**
     * Validates if the current request has a valid token.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException if no valid token exists
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->attributes->get(SetIsAdminRequestListener::ADMIN_FLAG_NAME) !== true) {
            return;
        }

        if ($this->csrfTokenValidator->validateCsrfToken($request, $this->csrfTokenId)) {
            return;
        }

        throw new AccessDeniedHttpException('Missing or invalid CSRF token');
    }
}
