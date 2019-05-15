<?php

declare(strict_types=1);

namespace Netgen\Layouts\Persistence\Values\Collection;

use Netgen\Layouts\Utils\HydratorTrait;

final class CollectionUpdateStruct
{
    use HydratorTrait;

    /**
     * Starting offset for the collection results.
     *
     * @var int|null
     */
    public $offset;

    /**
     * Starting limit for the collection results.
     *
     * Set to 0 to disable the limit.
     *
     * @var int|null
     */
    public $limit;

    /**
     * Flag indicating if the collection will be always available.
     *
     * @var bool|null
     */
    public $alwaysAvailable;

    /**
     * Flag indicating if the collection will be translatable.
     *
     * @var bool|null
     */
    public $isTranslatable;
}
