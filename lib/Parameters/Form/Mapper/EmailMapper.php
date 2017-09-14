<?php

namespace Netgen\BlockManager\Parameters\Form\Mapper;

use Netgen\BlockManager\Parameters\Form\Mapper;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class EmailMapper extends Mapper
{
    public function getFormType()
    {
        return EmailType::class;
    }
}
