<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\App\Item\ValueUrlGenerator;

use Netgen\Layouts\Item\ValueUrlGeneratorInterface;

/**
 * @implements \Netgen\Layouts\Item\ValueUrlGeneratorInterface<\Netgen\Layouts\Tests\App\Item\Value>
 */
final class MyValueTypeValueUrlGenerator implements ValueUrlGeneratorInterface
{
    public function generate(object $object): ?string
    {
        return '/value/' . $object->id . '/some/url';
    }
}
