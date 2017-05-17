<?php

namespace Netgen\BlockManager\Tests\Core\Values\Layout;

use Netgen\BlockManager\API\Values\Layout\LayoutUpdateStruct;
use PHPUnit\Framework\TestCase;

class LayoutUpdateStructTest extends TestCase
{
    public function testDefaultProperties()
    {
        $layoutUpdateStruct = new LayoutUpdateStruct();

        $this->assertNull($layoutUpdateStruct->name);
        $this->assertNull($layoutUpdateStruct->description);
    }

    public function testSetProperties()
    {
        $layoutUpdateStruct = new LayoutUpdateStruct(
            array(
                'name' => 'My layout',
                'description' => 'My description',
            )
        );

        $this->assertEquals('My layout', $layoutUpdateStruct->name);
        $this->assertEquals('My description', $layoutUpdateStruct->description);
    }
}
