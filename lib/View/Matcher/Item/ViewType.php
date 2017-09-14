<?php

namespace Netgen\BlockManager\View\Matcher\Item;

use Netgen\BlockManager\View\Matcher\MatcherInterface;
use Netgen\BlockManager\View\View\ItemViewInterface;
use Netgen\BlockManager\View\ViewInterface;

/**
 * This matcher matches if the view type of the item in the provided view
 * has a value specified in the configuration.
 */
class ViewType implements MatcherInterface
{
    public function match(ViewInterface $view, array $config)
    {
        if (!$view instanceof ItemViewInterface) {
            return false;
        }

        return in_array($view->getViewType(), $config, true);
    }
}
