<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsDebugBundle\EventListener\DataCollector;

use Netgen\BlockManager\Event\BlockManagerEvents;
use Netgen\BlockManager\Event\CollectViewParametersEvent;
use Netgen\BlockManager\View\View\BlockViewInterface;
use Netgen\Bundle\LayoutsDebugBundle\DataCollector\LayoutsDataCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class BlockViewListener implements EventSubscriberInterface
{
    /**
     * @var \Netgen\Bundle\LayoutsDebugBundle\DataCollector\LayoutsDataCollector
     */
    private $dataCollector;

    /**
     * @var string[]
     */
    private $enabledContexts;

    /**
     * @param \Netgen\Bundle\LayoutsDebugBundle\DataCollector\LayoutsDataCollector $dataCollector
     * @param string[] $enabledContexts
     */
    public function __construct(LayoutsDataCollector $dataCollector, array $enabledContexts)
    {
        $this->dataCollector = $dataCollector;
        $this->enabledContexts = $enabledContexts;
    }

    public static function getSubscribedEvents(): array
    {
        return [sprintf('%s.%s', BlockManagerEvents::BUILD_VIEW, 'block') => ['onBuildView', -65535]];
    }

    /**
     * Includes results built from all block collections, if specified so.
     */
    public function onBuildView(CollectViewParametersEvent $event): void
    {
        $view = $event->getView();

        if (!$view instanceof BlockViewInterface) {
            return;
        }

        if (!in_array($view->getContext(), $this->enabledContexts, true)) {
            return;
        }

        $this->dataCollector->collectBlockView($view);
    }
}
