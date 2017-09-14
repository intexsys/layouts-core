<?php

namespace Netgen\BlockManager\Parameters\Form\Mapper;

use Netgen\BlockManager\Parameters\Form\Mapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TextLineMapper extends Mapper
{
    public function getFormType()
    {
        return TextType::class;
    }
}
