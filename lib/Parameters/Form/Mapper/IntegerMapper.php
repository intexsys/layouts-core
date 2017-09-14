<?php

namespace Netgen\BlockManager\Parameters\Form\Mapper;

use Netgen\BlockManager\Parameters\Form\Mapper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class IntegerMapper extends Mapper
{
    public function getFormType()
    {
        return IntegerType::class;
    }
}
