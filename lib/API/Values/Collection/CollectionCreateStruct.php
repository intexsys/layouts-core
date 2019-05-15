<?php

declare(strict_types=1);

namespace Netgen\Layouts\API\Values\Collection;

final class CollectionCreateStruct
{
    /**
     * The offset for the collection.
     *
     * @var int
     */
    public $offset = 0;

    /**
     * The limit for the collection.
     *
     * @var int|null
     */
    public $limit;

    /**
     * If set, the collection will have a query created from this query struct.
     *
     * @var \Netgen\Layouts\API\Values\Collection\QueryCreateStruct|null
     */
    public $queryCreateStruct;
}
