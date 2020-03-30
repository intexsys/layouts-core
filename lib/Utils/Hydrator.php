<?php

declare(strict_types=1);

namespace Netgen\Layouts\Utils;

use Netgen\Layouts\Exception\RuntimeException;

final class Hydrator
{
    /**
     * Extract values from an object.
     *
     * @return array<string, mixed>
     */
    public function extract(object $object): array
    {
        return (function (): array {
            return get_object_vars($this);
        })->call($object);
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param array<string, mixed> $data
     * @param object $object
     *
     * @return mixed
     */
    public function hydrate(array $data, object $object)
    {
        return (function (array $data) {
            foreach ($data as $property => $value) {
                if (!property_exists($this, $property)) {
                    throw new RuntimeException(
                        sprintf(
                            'Property "%s" does not exist in "%s" class.',
                            $property,
                            get_debug_type($this)
                        )
                    );
                }

                $this->{$property} = $value;
            }

            return $this;
        })->call($object, $data);
    }
}
