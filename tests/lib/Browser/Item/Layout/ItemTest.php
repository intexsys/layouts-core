<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Browser\Item\Layout;

use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\Browser\Item\Layout\Item;
use PHPUnit\Framework\TestCase;

final class ItemTest extends TestCase
{
    /**
     * @var \Netgen\Layouts\API\Values\Layout\Layout
     */
    private $layout;

    /**
     * @var \Netgen\Layouts\Browser\Item\Layout\Item
     */
    private $item;

    public function setUp(): void
    {
        $this->layout = Layout::fromArray(['id' => 42, 'name' => 'My layout']);

        $this->item = new Item($this->layout);
    }

    /**
     * @covers \Netgen\Layouts\Browser\Item\Layout\Item::__construct
     * @covers \Netgen\Layouts\Browser\Item\Layout\Item::getValue
     */
    public function testGetValue(): void
    {
        self::assertSame(42, $this->item->getValue());
    }

    /**
     * @covers \Netgen\Layouts\Browser\Item\Layout\Item::getName
     */
    public function testGetName(): void
    {
        self::assertSame('My layout', $this->item->getName());
    }

    /**
     * @covers \Netgen\Layouts\Browser\Item\Layout\Item::isVisible
     */
    public function testIsVisible(): void
    {
        self::assertTrue($this->item->isVisible());
    }

    /**
     * @covers \Netgen\Layouts\Browser\Item\Layout\Item::isSelectable
     */
    public function testIsSelectable(): void
    {
        self::assertTrue($this->item->isSelectable());
    }

    /**
     * @covers \Netgen\Layouts\Browser\Item\Layout\Item::getLayout
     */
    public function testGetLayout(): void
    {
        self::assertSame($this->layout, $this->item->getLayout());
    }
}
