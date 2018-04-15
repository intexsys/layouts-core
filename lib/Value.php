<?php

namespace Netgen\BlockManager;

use Netgen\BlockManager\Exception\InvalidArgumentException;

abstract class Value
{
    /**
     * Creates the object and hydrates it with property values provided in $properties array.
     *
     * @param array $properties
     *
     * @throws \Netgen\BlockManager\Exception\InvalidArgumentException If one of the properties does not exist in the value
     */
    public function __construct(array $properties = array())
    {
        foreach ($properties as $property => $value) {
            if (!property_exists($this, $property)) {
                throw new InvalidArgumentException(
                    'properties',
                    sprintf(
                        'Property "%s" does not exist in "%s" class.',
                        $property,
                        get_class($this)
                    )
                );
            }

            $this->{$property} = $value;
        }
    }
}
