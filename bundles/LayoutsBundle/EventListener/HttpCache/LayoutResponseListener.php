<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\EventListener\HttpCache;

use Netgen\Layouts\HttpCache\TaggerInterface;
use Netgen\Layouts\View\View\LayoutViewInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class LayoutResponseListener implements EventSubscriberInterface
{
    /**
     * @var \Netgen\Layouts\HttpCache\TaggerInterface
     */
    private $tagger;

    /**
     * @var bool
     */
    private $isExceptionResponse = false;

    public function __construct(TaggerInterface $tagger)
    {
        $this->tagger = $tagger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 10],
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    /**
     * Tags the response with the data for layout provided by the event.
     *
     * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
     */
    public function onKernelResponse($event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $attributes = $event->getRequest()->attributes;

        $attributeName = 'nglLayoutView';
        if ($attributes->has('nglOverrideLayoutView')) {
            $attributeName = 'nglOverrideLayoutView';
        } elseif ($this->isExceptionResponse) {
            $attributeName = 'nglExceptionLayoutView';
        }

        $layoutView = $attributes->get($attributeName);
        if (!$layoutView instanceof LayoutViewInterface) {
            return;
        }

        $this->tagger->tagLayout($layoutView->getLayout());
    }

    /**
     * Tags the exception response with the data for layout provided by the event.
     *
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException($event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->isExceptionResponse = true;
    }
}
