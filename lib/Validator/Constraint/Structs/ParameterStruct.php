<?php

declare(strict_types=1);

namespace Netgen\Layouts\Validator\Constraint\Structs;

use Symfony\Component\Validator\Constraint;

final class ParameterStruct extends Constraint
{
    /**
     * @var string
     */
    public $message = 'netgen_block_manager.parameter_struct.invalid_value';

    /**
     * Parameter definition collection used to validate parameter values against.
     *
     * @var \Netgen\Layouts\Parameters\ParameterDefinitionCollectionInterface
     */
    public $parameterDefinitions;

    /**
     * If true, missing parameters will pass validation (e.g. when updating the value).
     *
     * @var bool
     */
    public $allowMissingFields = false;

    public function validatedBy(): string
    {
        return 'ngbm_parameter_struct';
    }
}
