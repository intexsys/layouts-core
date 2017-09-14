<?php

namespace Netgen\BlockManager\Persistence\Values\LayoutResolver;

use Netgen\BlockManager\ValueObject;

class TargetUpdateStruct extends ValueObject
{
    /**
     * New value of the target.
     *
     * @var int|string
     */
    public $value;
}
