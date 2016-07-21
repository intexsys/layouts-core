<?php

namespace Netgen\BlockManager\Tests\Core\Values;

use Netgen\BlockManager\API\Values\TargetCreateStruct;
use PHPUnit\Framework\TestCase;

class TargetCreateStructTest extends TestCase
{
    public function testDefaultProperties()
    {
        $targetCreateStruct = new TargetCreateStruct();

        $this->assertNull($targetCreateStruct->type);
        $this->assertNull($targetCreateStruct->value);
    }

    public function testSetProperties()
    {
        $targetCreateStruct = new TargetCreateStruct(
            array(
                'type' => 'target',
                'value' => 42,
            )
        );

        $this->assertEquals('target', $targetCreateStruct->type);
        $this->assertEquals(42, $targetCreateStruct->value);
    }
}
