<?php

namespace Netgen\BlockManager\Parameters\Form\Mapper;

use Netgen\BlockManager\Parameters\Form\Mapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class IdentifierMapper extends Mapper
{
    public function getFormType()
    {
        return TextType::class;
    }
}
