<?php

namespace Netgen\BlockManager\Serializer\Values;

/**
 * Represents a serialized value object together with a version.
 */
interface VersionedValueInterface extends ValueInterface
{
    /**
     * Returns the API version.
     *
     * @return int
     */
    public function getVersion();
}
