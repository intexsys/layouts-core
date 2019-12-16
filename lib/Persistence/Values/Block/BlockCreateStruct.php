<?php

declare(strict_types=1);

namespace Netgen\Layouts\Persistence\Values\Block;

use Netgen\Layouts\Utils\HydratorTrait;

final class BlockCreateStruct
{
    use HydratorTrait;

    /**
     * Status of the new block.
     *
     * @var int
     */
    public $status;

    /**
     * Position of the new block.
     *
     * @var int
     */
    public $position;

    /**
     * Identifier of the block definition of the new block.
     *
     * @var string
     */
    public $definitionIdentifier;

    /**
     * View type of the new block.
     *
     * @var string
     */
    public $viewType;

    /**
     * Item view type of the new block.
     *
     * @var string
     */
    public $itemViewType;

    /**
     * Human readable name of the new block.
     *
     * @var string
     */
    public $name;

    /**
     * Flag indicating if the block is always available.
     *
     * @var bool
     */
    public $alwaysAvailable;

    /**
     * Flag indicating if the block is translatable.
     *
     * @var bool
     */
    public $isTranslatable;

    /**
     * The block parameters.
     *
     * @var array<string, mixed>
     */
    public $parameters;

    /**
     * The block configuration.
     *
     * @var array<string, array<string, mixed>>
     */
    public $config;
}
