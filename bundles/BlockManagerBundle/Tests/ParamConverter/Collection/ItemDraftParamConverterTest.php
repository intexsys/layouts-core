<?php

namespace Netgen\Bundle\BlockManagerBundle\Tests\ParamConverter\Collection;

use Netgen\Bundle\BlockManagerBundle\ParamConverter\Collection\ItemDraftParamConverter;
use Netgen\BlockManager\Core\Values\Collection\ItemDraft;
use Netgen\BlockManager\API\Values\Collection\ItemDraft as APIItemDraft;
use Netgen\BlockManager\API\Service\CollectionService;
use PHPUnit\Framework\TestCase;

class ItemDraftParamConverterTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionServiceMock;

    /**
     * @var \Netgen\Bundle\BlockManagerBundle\ParamConverter\Collection\ItemDraftParamConverter
     */
    protected $paramConverter;

    public function setUp()
    {
        $this->collectionServiceMock = $this->createMock(CollectionService::class);

        $this->paramConverter = new ItemDraftParamConverter($this->collectionServiceMock);
    }

    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\ParamConverter\Collection\ItemDraftParamConverter::getSourceAttributeNames
     */
    public function testGetSourceAttributeName()
    {
        $this->assertEquals(array('itemId'), $this->paramConverter->getSourceAttributeNames());
    }

    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\ParamConverter\Collection\ItemDraftParamConverter::getDestinationAttributeName
     */
    public function testGetDestinationAttributeName()
    {
        $this->assertEquals('item', $this->paramConverter->getDestinationAttributeName());
    }

    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\ParamConverter\Collection\ItemDraftParamConverter::getSupportedClass
     */
    public function testGetSupportedClass()
    {
        $this->assertEquals(APIItemDraft::class, $this->paramConverter->getSupportedClass());
    }

    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\ParamConverter\Collection\ItemDraftParamConverter::__construct
     * @covers \Netgen\Bundle\BlockManagerBundle\ParamConverter\Collection\ItemDraftParamConverter::loadValueObject
     */
    public function testLoadValueObject()
    {
        $item = new ItemDraft();

        $this->collectionServiceMock
            ->expects($this->once())
            ->method('loadItemDraft')
            ->with($this->equalTo(42))
            ->will($this->returnValue($item));

        $this->assertEquals($item, $this->paramConverter->loadValueObject(array('itemId' => 42)));
    }
}
