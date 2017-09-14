<?php

namespace Netgen\BlockManager\Core\Values\Block;

use Netgen\BlockManager\API\Values\Block\BlockTranslation as APIBlockTranslation;
use Netgen\BlockManager\Core\Values\ParameterBasedValueTrait;
use Netgen\BlockManager\ValueObject;

class BlockTranslation extends ValueObject implements APIBlockTranslation
{
    use ParameterBasedValueTrait;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var bool
     */
    protected $isMainTranslation;

    public function getLocale()
    {
        return $this->locale;
    }

    public function isMainTranslation()
    {
        return $this->isMainTranslation;
    }
}
