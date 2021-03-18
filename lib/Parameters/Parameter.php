<?php

declare(strict_types=1);

namespace Netgen\Layouts\Parameters;

use Netgen\Layouts\Utils\HydratorTrait;
use function is_array;
use function is_object;
use function method_exists;

final class Parameter
{
    use HydratorTrait;

    private string $name;

    private ParameterDefinition $parameterDefinition;

    /**
     * @var mixed
     */
    private $value;

    private bool $isEmpty;

    /**
     * Returns the string representation of the parameter value.
     */
    public function __toString(): string
    {
        if (is_array($this->value) || (is_object($this->value) && !method_exists($this->value, '__toString'))) {
            return '';
        }

        return (string) $this->value;
    }

    /**
     * Returns the parameter name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the parameter definition.
     */
    public function getParameterDefinition(): ParameterDefinition
    {
        return $this->parameterDefinition;
    }

    /**
     * Returns the parameter value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns if the parameter value is empty.
     */
    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }
}
