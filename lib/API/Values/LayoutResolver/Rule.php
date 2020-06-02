<?php

declare(strict_types=1);

namespace Netgen\Layouts\API\Values\LayoutResolver;

use Doctrine\Common\Collections\ArrayCollection;
use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\API\Values\LazyPropertyTrait;
use Netgen\Layouts\API\Values\Value;
use Netgen\Layouts\API\Values\ValueStatusTrait;
use Netgen\Layouts\Utils\HydratorTrait;
use Ramsey\Uuid\UuidInterface;

final class Rule implements Value
{
    use HydratorTrait;
    use LazyPropertyTrait;
    use ValueStatusTrait;

    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $id;

    /**
     * @var \Netgen\Layouts\API\Values\Layout\Layout|null
     */
    private $layout;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Netgen\Layouts\API\Values\LayoutResolver\Target>
     */
    private $targets;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Netgen\Layouts\API\Values\LayoutResolver\Condition>
     */
    private $conditions;

    public function __construct()
    {
        $this->targets = $this->targets ?? new ArrayCollection();
        $this->conditions = $this->conditions ?? new ArrayCollection();
    }

    /**
     * Returns the rule UUID.
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Returns the layout mapped to this rule.
     */
    public function getLayout(): ?Layout
    {
        return $this->getLazyProperty($this->layout);
    }

    /**
     * Returns if the rule is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Returns the rule priority.
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Returns the rule comment.
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * Returns all the targets in the rule.
     */
    public function getTargets(): TargetList
    {
        return new TargetList($this->targets->toArray());
    }

    /**
     * Returns all conditions in the rule.
     */
    public function getConditions(): ConditionList
    {
        return new ConditionList($this->conditions->toArray());
    }
}
