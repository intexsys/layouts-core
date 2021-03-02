<?php

declare(strict_types=1);

namespace Netgen\Layouts\API\Values\LayoutResolver;

final class RuleGroupUpdateStruct
{
    /**
     * New human readable name of the rule group.
     */
    public ?string $name = null;

    /**
     * Description of the rule group.
     */
    public ?string $comment = null;
}
