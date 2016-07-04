<?php

namespace Netgen\BlockManager\Persistence\Values;

use Netgen\BlockManager\ValueObject;

class ConditionCreateStruct extends ValueObject
{
    /**
     * @var int|string
     */
    public $ruleId;

    /**
     * @var int
     */
    public $status;

    /**
     * @var string
     */
    public $type;

    /**
     * @var mixed
     */
    public $value;
}
