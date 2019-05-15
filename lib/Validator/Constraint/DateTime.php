<?php

declare(strict_types=1);

namespace Netgen\Layouts\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class DateTime extends Constraint
{
    /**
     * If true, the validator will validate an array with datetime and timezone
     * keys as a datetime value.
     *
     * @var bool
     */
    public $allowArray = false;

    /**
     * @var string
     */
    public $invalidTimeZoneMessage = 'netgen_layouts.datetime.invalid_timezone';

    public function validatedBy(): string
    {
        return 'nglayouts_datetime';
    }
}
