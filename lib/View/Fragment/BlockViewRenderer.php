<?php

namespace Netgen\BlockManager\View\Fragment;

use Netgen\BlockManager\HttpCache\Block\CacheableResolverInterface;
use Netgen\BlockManager\View\View\BlockViewInterface;
use Netgen\BlockManager\View\ViewInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class BlockViewRenderer implements ViewRendererInterface
{
    /**
     * @var \Netgen\BlockManager\HttpCache\Block\CacheableResolverInterface
     */
    protected $cacheableResolver;

    /**
     * @var string
     */
    protected $blockController;

    /**
     * @var array
     */
    protected $supportedContexts;

    /**
     * Constructor.
     *
     * @param \Netgen\BlockManager\HttpCache\Block\CacheableResolverInterface $cacheableResolver
     * @param string $blockController
     * @param array $supportedContexts
     */
    public function __construct(
        CacheableResolverInterface $cacheableResolver,
        $blockController,
        array $supportedContexts = array(ViewInterface::CONTEXT_DEFAULT)
    ) {
        $this->cacheableResolver = $cacheableResolver;
        $this->blockController = $blockController;
        $this->supportedContexts = $supportedContexts;
    }

    /**
     * Returns if the view renderer supports the view.
     *
     * @param \Netgen\BlockManager\View\ViewInterface $view
     *
     * @return bool
     */
    public function supportsView(ViewInterface $view)
    {
        if (!$view instanceof BlockViewInterface) {
            return false;
        }

        if (!in_array($view->getContext(), $this->supportedContexts, true)) {
            return false;
        }

        return $this->cacheableResolver->isCacheable($view->getBlock());
    }

    /**
     * Returns the controller that will be used to render the fragment.
     *
     * @param \Netgen\BlockManager\View\ViewInterface $view
     *
     * @return \Symfony\Component\HttpKernel\Controller\ControllerReference
     */
    public function getController(ViewInterface $view)
    {
        /* @var \Netgen\BlockManager\View\View\BlockViewInterface $view */

        return new ControllerReference(
            $this->blockController,
            array(
                'blockId' => $view->getBlock()->getId(),
                'context' => $view->getContext(),
                '_ngbm_status' => 'published',
            )
        );
    }
}
