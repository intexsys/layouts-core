<?php

namespace Netgen\BlockManager\View\View;

use Netgen\BlockManager\View\View;

class PlaceholderView extends View implements PlaceholderViewInterface
{
    public function getPlaceholder()
    {
        return $this->parameters['placeholder'];
    }

    public function getBlock()
    {
        return $this->parameters['block'];
    }

    public function getIdentifier()
    {
        return 'placeholder_view';
    }
}
