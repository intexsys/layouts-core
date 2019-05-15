<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\API\Values\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Netgen\Layouts\API\Values\Collection\Collection;
use Netgen\Layouts\API\Values\Collection\Item;
use Netgen\Layouts\API\Values\Collection\ItemList;
use Netgen\Layouts\API\Values\Collection\Query;
use Netgen\Layouts\API\Values\Value;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class CollectionTest extends TestCase
{
    public function testInstance(): void
    {
        self::assertInstanceOf(Value::class, new Collection());
    }

    /**
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::__construct
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getAvailableLocales
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getItems
     */
    public function testDefaultProperties(): void
    {
        $collection = new Collection();

        self::assertSame([], $collection->getAvailableLocales());
        self::assertCount(0, $collection->getItems());
    }

    /**
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::__construct
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getAvailableLocales
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getId
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getItem
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getItems
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getLimit
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getLocale
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getMainLocale
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getOffset
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getQuery
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::hasItem
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::hasQuery
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::isAlwaysAvailable
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::isTranslatable
     */
    public function testSetProperties(): void
    {
        $items = [
            Item::fromArray(['position' => 3]),
            Item::fromArray(['position' => 5]),
        ];

        $query = new Query();

        $uuid = Uuid::uuid4();

        $collection = Collection::fromArray(
            [
                'id' => $uuid,
                'offset' => 5,
                'limit' => 10,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'isTranslatable' => true,
                'alwaysAvailable' => false,
                'locale' => 'en',
                'items' => new ArrayCollection($items),
                'query' => $query,
            ]
        );

        self::assertSame($uuid->toString(), $collection->getId()->toString());
        self::assertSame(5, $collection->getOffset());
        self::assertSame(10, $collection->getLimit());
        self::assertSame('en', $collection->getMainLocale());
        self::assertSame(['en', 'hr'], $collection->getAvailableLocales());
        self::assertTrue($collection->isTranslatable());
        self::assertFalse($collection->isAlwaysAvailable());
        self::assertSame('en', $collection->getLocale());

        self::assertCount(2, $collection->getItems());
        self::assertSame($items[0], $collection->getItems()[0]);
        self::assertSame($items[1], $collection->getItems()[1]);

        self::assertSame($query, $collection->getQuery());
        self::assertTrue($collection->hasQuery());

        self::assertFalse($collection->hasItem(2));
        self::assertTrue($collection->hasItem(3));
        self::assertTrue($collection->hasItem(5));

        self::assertSame($items[0], $collection->getItem(3));
        self::assertSame($items[1], $collection->getItem(5));
    }

    /**
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getOffset
     */
    public function testGetOffsetForManualCollection(): void
    {
        $collection = Collection::fromArray(
            [
                'id' => Uuid::uuid4(),
                'offset' => 5,
            ]
        );

        self::assertSame(0, $collection->getOffset());
    }

    /**
     * @covers \Netgen\Layouts\API\Values\Collection\Collection::getItem
     */
    public function testGetItemWithNonExistingPosition(): void
    {
        $collection = Collection::fromArray(
            [
                'id' => Uuid::uuid4(),
                'items' => new ItemList([Item::fromArray(['position' => 0])]),
            ]
        );

        self::assertNull($collection->getItem(999));
    }
}
