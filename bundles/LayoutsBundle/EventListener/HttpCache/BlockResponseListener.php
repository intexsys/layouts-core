<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\EventListener\HttpCache;

use Netgen\BlockManager\HttpCache\TaggerInterface;
use Netgen\BlockManager\View\View\BlockViewInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class BlockResponseListener implements EventSubscriberInterface
{
    /**
     * @var \Netgen\BlockManager\HttpCache\TaggerInterface
     */
    private $tagger;

    public function __construct(TaggerInterface $tagger)
    {
        $this->tagger = $tagger;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => ['onKernelResponse', -255]];
    }

    /**
     * Tags the response with the data for block provided by the event.
     */
    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $blockView = $event->getRequest()->attributes->get('ngbmView');
        if (!$blockView instanceof BlockViewInterface) {
            return;
        }

        $this->tagger->tagBlock($event->getResponse(), $blockView->getBlock());
    }
}
