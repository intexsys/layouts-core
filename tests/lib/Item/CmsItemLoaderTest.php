<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Tests\Item;

use Netgen\BlockManager\Exception\Item\ItemException;
use Netgen\BlockManager\Item\CmsItem;
use Netgen\BlockManager\Item\CmsItemBuilderInterface;
use Netgen\BlockManager\Item\CmsItemLoader;
use Netgen\BlockManager\Item\NullCmsItem;
use Netgen\BlockManager\Tests\Item\Stubs\Value;
use Netgen\BlockManager\Tests\Item\Stubs\ValueLoader;
use PHPUnit\Framework\TestCase;

final class CmsItemLoaderTest extends TestCase
{
    /**
     * @var \Netgen\BlockManager\Item\CmsItemBuilderInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $cmsItemBuilderMock;

    /**
     * @var \Netgen\BlockManager\Item\CmsItemLoaderInterface
     */
    private $cmsItemLoader;

    public function setUp(): void
    {
        $this->cmsItemBuilderMock = $this->createMock(CmsItemBuilderInterface::class);
    }

    /**
     * @covers \Netgen\BlockManager\Item\CmsItemLoader::__construct
     * @covers \Netgen\BlockManager\Item\CmsItemLoader::load
     */
    public function testLoad(): void
    {
        $item = CmsItem::fromArray(
            [
                'value' => 42,
                'remoteId' => 'abc',
                'name' => 'Some value',
                'valueType' => 'value',
                'isVisible' => true,
                'object' => new Value(42, 'abc'),
            ]
        );

        $this->cmsItemLoader = new CmsItemLoader(
            $this->cmsItemBuilderMock,
            ['value' => new ValueLoader(true)]
        );

        $this->cmsItemBuilderMock
            ->expects(self::any())
            ->method('build')
            ->willReturn($item);

        self::assertSame($item, $this->cmsItemLoader->load(42, 'value'));
    }

    /**
     * @covers \Netgen\BlockManager\Item\CmsItemLoader::load
     */
    public function testLoadItemWithNoItem(): void
    {
        $this->cmsItemLoader = new CmsItemLoader(
            $this->cmsItemBuilderMock,
            ['value' => new ValueLoader(false)]
        );

        $loadedValue = $this->cmsItemLoader->load(42, 'value');

        self::assertInstanceOf(NullCmsItem::class, $loadedValue);
        self::assertSame('value', $loadedValue->getValueType());
    }

    /**
     * @covers \Netgen\BlockManager\Item\CmsItemLoader::load
     */
    public function testLoadItemThrowsItemException(): void
    {
        $this->expectException(ItemException::class);
        $this->expectExceptionMessage('Value type "value" does not exist.');

        $this->cmsItemLoader = new CmsItemLoader($this->cmsItemBuilderMock, []);

        $this->cmsItemLoader->load(42, 'value');
    }

    /**
     * @covers \Netgen\BlockManager\Item\CmsItemLoader::loadByRemoteId
     */
    public function testLoadByRemoteId(): void
    {
        $item = CmsItem::fromArray(
            [
                'value' => 42,
                'remoteId' => 'abc',
                'name' => 'Some value',
                'valueType' => 'value',
                'isVisible' => true,
                'object' => new Value(42, 'abc'),
            ]
        );

        $this->cmsItemLoader = new CmsItemLoader(
            $this->cmsItemBuilderMock,
            ['value' => new ValueLoader(true)]
        );

        $this->cmsItemBuilderMock
            ->expects(self::any())
            ->method('build')
            ->willReturn($item);

        self::assertSame($item, $this->cmsItemLoader->loadByRemoteId(42, 'value'));
    }

    /**
     * @covers \Netgen\BlockManager\Item\CmsItemLoader::loadByRemoteId
     */
    public function testLoadByRemoteIdItemThrowsItemExceptionWithNoItem(): void
    {
        $this->cmsItemLoader = new CmsItemLoader(
            $this->cmsItemBuilderMock,
            ['value' => new ValueLoader(false)]
        );

        $loadedValue = $this->cmsItemLoader->loadByRemoteId(42, 'value');

        self::assertInstanceOf(NullCmsItem::class, $loadedValue);
        self::assertSame('value', $loadedValue->getValueType());
    }

    /**
     * @covers \Netgen\BlockManager\Item\CmsItemLoader::loadByRemoteId
     */
    public function testLoadByRemoteIdItemThrowsItemException(): void
    {
        $this->expectException(ItemException::class);
        $this->expectExceptionMessage('Value type "value" does not exist.');

        $this->cmsItemLoader = new CmsItemLoader($this->cmsItemBuilderMock, []);

        $this->cmsItemLoader->loadByRemoteId(42, 'value');
    }
}
