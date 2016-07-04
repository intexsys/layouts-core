<?php

namespace Netgen\BlockManager\Tests\Persistence\Values\Rule;

use Netgen\BlockManager\Persistence\Values\LayoutResolver\Rule;
use Netgen\BlockManager\Persistence\Values\LayoutResolver\Target;
use PHPUnit\Framework\TestCase;

class TargetTest extends TestCase
{
    public function testSetDefaultProperties()
    {
        $target = new Target();

        self::assertNull($target->id);
        self::assertNull($target->ruleId);
        self::assertNull($target->type);
        self::assertNull($target->value);
        self::assertNull($target->status);
    }

    public function testSetProperties()
    {
        $target = new Target(
            array(
                'id' => 42,
                'ruleId' => 30,
                'type' => 'target',
                'value' => 32,
                'status' => Rule::STATUS_PUBLISHED,
            )
        );

        self::assertEquals(42, $target->id);
        self::assertEquals(30, $target->ruleId);
        self::assertEquals('target', $target->type);
        self::assertEquals(32, $target->value);
        self::assertEquals(Rule::STATUS_PUBLISHED, $target->status);
    }
}
