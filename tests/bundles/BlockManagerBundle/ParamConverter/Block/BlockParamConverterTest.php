<?php

declare(strict_types=1);

namespace Netgen\Bundle\BlockManagerBundle\Tests\ParamConverter\Block;

use Netgen\BlockManager\API\Service\BlockService;
use Netgen\BlockManager\API\Values\Block\Block;
use Netgen\Bundle\BlockManagerBundle\ParamConverter\Block\BlockParamConverter;
use PHPUnit\Framework\TestCase;

final class BlockParamConverterTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $blockServiceMock;

    /**
     * @var \Netgen\Bundle\BlockManagerBundle\ParamConverter\Block\BlockParamConverter
     */
    private $paramConverter;

    public function setUp(): void
    {
        $this->blockServiceMock = $this->createMock(BlockService::class);

        $this->paramConverter = new BlockParamConverter($this->blockServiceMock);
    }

    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\ParamConverter\Block\BlockParamConverter::__construct
     * @covers \Netgen\Bundle\BlockManagerBundle\ParamConverter\Block\BlockParamConverter::getSourceAttributeNames
     */
    public function testGetSourceAttributeName(): void
    {
        self::assertSame(['blockId'], $this->paramConverter->getSourceAttributeNames());
    }

    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\ParamConverter\Block\BlockParamConverter::getDestinationAttributeName
     */
    public function testGetDestinationAttributeName(): void
    {
        self::assertSame('block', $this->paramConverter->getDestinationAttributeName());
    }

    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\ParamConverter\Block\BlockParamConverter::getSupportedClass
     */
    public function testGetSupportedClass(): void
    {
        self::assertSame(Block::class, $this->paramConverter->getSupportedClass());
    }

    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\ParamConverter\Block\BlockParamConverter::loadValue
     */
    public function testLoadValue(): void
    {
        $block = new Block();

        $this->blockServiceMock
            ->expects(self::once())
            ->method('loadBlock')
            ->with(self::identicalTo(42))
            ->willReturn($block);

        self::assertSame(
            $block,
            $this->paramConverter->loadValue(
                [
                    'blockId' => 42,
                    'status' => 'published',
                ]
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\ParamConverter\Block\BlockParamConverter::loadValue
     */
    public function testLoadValueDraft(): void
    {
        $block = new Block();

        $this->blockServiceMock
            ->expects(self::once())
            ->method('loadBlockDraft')
            ->with(self::identicalTo(42))
            ->willReturn($block);

        self::assertSame(
            $block,
            $this->paramConverter->loadValue(
                [
                    'blockId' => 42,
                    'status' => 'draft',
                ]
            )
        );
    }
}
