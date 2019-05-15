<?php

declare(strict_types=1);

namespace Netgen\Layouts\Validator\Constraint\Parameters;

use Symfony\Component\Validator\Constraint;

final class ItemLink extends Constraint
{
    /**
     * @var string
     */
    public $message = 'netgen_layouts.item_link.no_item';

    /**
     * @var string
     */
    public $invalidItemMessage = 'netgen_layouts.item_link.invalid_item';

    /**
     * @var string
     */
    public $valueTypeNotAllowedMessage = 'netgen_layouts.item_link.value_type_not_allowed';

    /**
     * If set to true, the constraint will accept values for invalid or non existing items.
     *
     * @var bool
     */
    public $allowInvalid = false;

    /**
     * If not empty, will limit valid value types to the specified list.
     *
     * @var array
     */
    public $valueTypes = [];

    public function validatedBy(): string
    {
        return 'nglayouts_item_link';
    }
}
