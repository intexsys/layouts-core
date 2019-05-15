<?php

declare(strict_types=1);

namespace Netgen\Bundle\BlockManagerBundle\EventListener\HttpCache;

use Netgen\BlockManager\HttpCache\ClientInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class InvalidationListener implements EventSubscriberInterface
{
    /**
     * @var \Netgen\BlockManager\HttpCache\ClientInterface
     */
    private $httpCacheClient;

    public function __construct(ClientInterface $httpCacheClient)
    {
        $this->httpCacheClient = $httpCacheClient;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => 'onKernelTerminate',
            KernelEvents::EXCEPTION => 'onKernelException',
            ConsoleEvents::TERMINATE => 'onConsoleTerminate',
            // ConsoleEvents::EXCEPTION is @deprecated in Symfony 3.3 and removed in Symfony 4.0,
            // so we're using a string instead of constant in order to support previous versions.
            // It is replaced with ConsoleEvents::ERROR, so we're listening for that event too.
            // Remove when support for Symfony 3.4 ends
            'console.exception' => 'onConsoleTerminate',
            'console.error' => 'onConsoleTerminate',
        ];
    }

    /**
     * Commits all the collected invalidation requests.
     */
    public function onKernelTerminate(PostResponseEvent $event): void
    {
        $this->httpCacheClient->commit();
    }

    /**
     * Commits all the collected invalidation requests.
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $this->httpCacheClient->commit();
    }

    /**
     * Commits all the collected invalidation requests.
     */
    public function onConsoleTerminate(ConsoleEvent $event): void
    {
        $this->httpCacheClient->commit();
    }
}
