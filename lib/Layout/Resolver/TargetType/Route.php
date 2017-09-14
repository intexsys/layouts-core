<?php

namespace Netgen\BlockManager\Layout\Resolver\TargetType;

use Netgen\BlockManager\Layout\Resolver\TargetTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

class Route implements TargetTypeInterface
{
    public function getType()
    {
        return 'route';
    }

    public function getConstraints()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\Type(array('type' => 'string')),
        );
    }

    public function provideValue(Request $request)
    {
        return $request->attributes->get('_route');
    }
}
