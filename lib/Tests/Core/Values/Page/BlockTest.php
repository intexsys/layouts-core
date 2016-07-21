<?php

namespace Netgen\BlockManager\Tests\Core\Values\Page;

use Netgen\BlockManager\API\Values\Page\Layout;
use Netgen\BlockManager\Core\Values\Page\Block;
use PHPUnit\Framework\TestCase;

class BlockTest extends TestCase
{
    /**
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::__construct
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getId
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getLayoutId
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getZoneIdentifier
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getPosition
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getDefinitionIdentifier
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getParameters
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getParameter
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::hasParameter
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getViewType
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getItemViewType
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getName
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getStatus
     */
    public function testSetDefaultProperties()
    {
        $block = new Block();

        $this->assertNull($block->getId());
        $this->assertNull($block->getLayoutId());
        $this->assertNull($block->getZoneIdentifier());
        $this->assertNull($block->getPosition());
        $this->assertNull($block->getDefinitionIdentifier());
        $this->assertEquals(array(), $block->getParameters());
        $this->assertNull($block->getParameter('test'));
        $this->assertFalse($block->hasParameter('test'));
        $this->assertNull($block->getViewType());
        $this->assertNull($block->getItemViewType());
        $this->assertNull($block->getName());
        $this->assertNull($block->getStatus());
    }

    /**
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::__construct
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getId
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getLayoutId
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getZoneIdentifier
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getPosition
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getDefinitionIdentifier
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getParameters
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getParameter
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::hasParameter
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getViewType
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getItemViewType
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getName
     * @covers \Netgen\BlockManager\Core\Values\Page\Block::getStatus
     */
    public function testSetProperties()
    {
        $block = new Block(
            array(
                'id' => 42,
                'layoutId' => 84,
                'zoneIdentifier' => 'left',
                'position' => 3,
                'definitionIdentifier' => 'text',
                'parameters' => array(
                    'some_param' => 'some_value',
                    'some_other_param' => 'some_other_value',
                ),
                'viewType' => 'default',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'status' => Layout::STATUS_PUBLISHED,
            )
        );

        $this->assertEquals(42, $block->getId());
        $this->assertEquals(84, $block->getLayoutId());
        $this->assertEquals('left', $block->getZoneIdentifier());
        $this->assertEquals(3, $block->getPosition());
        $this->assertEquals('text', $block->getDefinitionIdentifier());
        $this->assertEquals(
            array(
                'some_param' => 'some_value',
                'some_other_param' => 'some_other_value',
            ),
            $block->getParameters()
        );
        $this->assertNull($block->getParameter('test'));
        $this->assertEquals('some_value', $block->getParameter('some_param'));
        $this->assertFalse($block->hasParameter('test'));
        $this->assertTrue($block->hasParameter('some_param'));
        $this->assertEquals('default', $block->getViewType());
        $this->assertEquals('standard', $block->getItemViewType());
        $this->assertEquals('My block', $block->getName());
        $this->assertEquals(Layout::STATUS_PUBLISHED, $block->getStatus());
    }
}
