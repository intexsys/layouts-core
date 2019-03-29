<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Tests\Bundle\FixturesBundle\Item\ValueConverter;

use Netgen\BlockManager\Item\ValueConverterInterface;
use Netgen\BlockManager\Tests\Bundle\FixturesBundle\Item\Value;

final class MyValueTypeValueConverter implements ValueConverterInterface
{
    public function supports($object): bool
    {
        return $object instanceof Value;
    }

    public function getValueType($object): string
    {
        return 'my_value_type';
    }

    public function getId($object)
    {
        return $object->id;
    }

    public function getRemoteId($object)
    {
        return $object->id;
    }

    public function getName($object): string
    {
        return 'Value with ID #' . $object->id;
    }

    public function getIsVisible($object): bool
    {
        return true;
    }

    public function getObject($object)
    {
        return $object;
    }
}
