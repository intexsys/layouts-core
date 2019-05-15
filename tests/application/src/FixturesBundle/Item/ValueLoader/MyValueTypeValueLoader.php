<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Tests\Bundle\FixturesBundle\Item\ValueLoader;

use Netgen\BlockManager\Item\ValueLoaderInterface;
use Netgen\BlockManager\Tests\Bundle\FixturesBundle\Item\Value;

final class MyValueTypeValueLoader implements ValueLoaderInterface
{
    public function load($id): ?object
    {
        return new Value((int) $id);
    }

    public function loadByRemoteId($remoteId): ?object
    {
        return new Value((int) $remoteId);
    }
}
