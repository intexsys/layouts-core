<?php

namespace Netgen\BlockManager\Tests\Block\Stubs;

use Netgen\BlockManager\API\Values\Page\Block;
use Netgen\BlockManager\Block\TwigBlockDefinitionInterface;

class TwigBlockDefinition extends BlockDefinition implements TwigBlockDefinitionInterface
{
    /**
     * Returns the name of the Twig block to use.
     *
     * @param \Netgen\BlockManager\API\Values\Page\Block $block
     *
     * @return string
     */
    public function getTwigBlockName(Block $block)
    {
        return $block->getParameter('block_name')->getValue();
    }
}
