<?php

namespace Netgen\BlockManager\Tests\Core\Values\LayoutResolver;

use Netgen\BlockManager\Core\Values\LayoutResolver\Target;
use Netgen\BlockManager\Core\Values\LayoutResolver\Rule;
use Netgen\BlockManager\Core\Values\LayoutResolver\Condition;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::__construct
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getId
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getStatus
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getLayoutId
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getPriority
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::isEnabled
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getComment
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getTargets
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getConditions
     */
    public function testSetDefaultProperties()
    {
        $rule = new Rule();

        self::assertNull($rule->getId());
        self::assertNull($rule->getStatus());
        self::assertNull($rule->getLayoutId());
        self::assertNull($rule->getPriority());
        self::assertFalse($rule->isEnabled());
        self::assertNull($rule->getComment());
        self::assertEquals(array(), $rule->getTargets());
        self::assertEquals(array(), $rule->getConditions());
    }

    /**
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::__construct
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getId
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getStatus
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getLayoutId
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getPriority
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::isEnabled
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getComment
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getTargets
     * @covers \Netgen\BlockManager\Core\Values\LayoutResolver\Rule::getConditions
     */
    public function testSetProperties()
    {
        $rule = new Rule(
            array(
                'id' => 42,
                'status' => Rule::STATUS_PUBLISHED,
                'layoutId' => 24,
                'priority' => 13,
                'enabled' => true,
                'comment' => 'Comment',
                'targets' => array(new Target(), new Target()),
                'conditions' => array(new Condition()),
            )
        );

        self::assertEquals(42, $rule->getId());
        self::assertEquals(Rule::STATUS_PUBLISHED, $rule->getStatus());
        self::assertEquals(24, $rule->getLayoutId());
        self::assertEquals(13, $rule->getPriority());
        self::assertTrue($rule->isEnabled());
        self::assertEquals('Comment', $rule->getComment());
        self::assertCount(2, $rule->getTargets());
        self::assertCount(1, $rule->getConditions());
    }
}
