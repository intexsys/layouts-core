<?php

namespace Netgen\BlockManager\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class ValueType extends Constraint
{
    /**
     * @var string
     */
    public $message = 'netgen_block_manager.value_type.no_value_type';

    public function validatedBy()
    {
        return 'ngbm_value_type';
    }
}
