<?php

declare(strict_types=1);

namespace Netgen\Layouts\API\Values\LayoutResolver;

use Netgen\Layouts\API\Values\Value;
use Netgen\Layouts\Layout\Resolver\ConditionTypeInterface;
use Netgen\Layouts\Utils\HydratorTrait;
use Ramsey\Uuid\UuidInterface;

abstract class Condition implements Value
{
    use HydratorTrait;

    protected UuidInterface $id;

    protected ConditionTypeInterface $conditionType;

    /**
     * @var mixed
     */
    protected $value;

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Returns the condition type.
     */
    public function getConditionType(): ConditionTypeInterface
    {
        return $this->conditionType;
    }

    /**
     * Returns the condition value.
     *
     * Value of the condition can be a scalar, an associative array, numeric array or a nested
     * combination of these.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
