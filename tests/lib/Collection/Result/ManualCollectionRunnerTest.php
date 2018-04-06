<?php

namespace Netgen\BlockManager\Tests\Collection\Result;

use Netgen\BlockManager\Collection\Result\CollectionRunnerFactory;
use Netgen\BlockManager\Collection\Result\Result;
use Netgen\BlockManager\Collection\Result\ResultSet;
use Netgen\BlockManager\Item\Item;
use Netgen\BlockManager\Item\ItemBuilderInterface;
use Netgen\BlockManager\Tests\Collection\Stubs\Collection;
use PHPUnit\Framework\TestCase;

final class ManualCollectionRunnerTest extends TestCase
{
    use IteratorTestTrait;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $itemBuilderMock;

    public function setUp()
    {
        $this->itemBuilderMock = $this->createMock(ItemBuilderInterface::class);
    }

    /**
     * @param array $collectionItems
     * @param array $values
     * @param int $totalCount
     * @param int $offset
     * @param int $limit
     * @param int $flags
     *
     * @covers \Netgen\BlockManager\Collection\Result\ManualCollectionRunner::count
     * @covers \Netgen\BlockManager\Collection\Result\ManualCollectionRunner::__invoke
     *
     * @dataProvider manualCollectionProvider
     */
    public function testCollectionResult(array $collectionItems, array $values, $totalCount, $offset = 0, $limit = 200, $flags = 0)
    {
        $collection = new Collection($collectionItems);
        $factory = new CollectionRunnerFactory($this->itemBuilderMock);
        $collectionRunner = $factory->getCollectionRunner($collection);
        $expectedValues = $this->buildExpectedValues($values);

        $this->assertEquals($totalCount, $collectionRunner->count($collection));
        $this->assertIteratorValues($expectedValues, $collectionRunner($collection, $offset, $limit, $flags));
    }

    /**
     * Builds data providers for building result from manual collection.
     *
     * IDs are identifiers of 3rd party values (for example eZ content)
     *
     * @return array
     */
    public function manualCollectionProvider()
    {
        return array(
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(42, 43, 44, 45, 46),
                13,
                0,
                5,
            ),
            array(
                array(42, 43, null, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(42, 43, 45, 46, 47),
                12,
                0,
                5,
            ),
            array(
                array(42, 43, null, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(42, 43, null, 45, 46, 47),
                12,
                0,
                5,
                ResultSet::INCLUDE_INVALID_ITEMS,
            ),
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, null, 51, 52, 53, 54),
                array(42, 43, 44, 45, 46),
                12,
                0,
                5,
            ),
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, null, 51, 52, 53, 54),
                array(42, 43, 44, 45, 46),
                12,
                0,
                5,
                ResultSet::INCLUDE_INVALID_ITEMS,
            ),
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                13,
            ),
            array(
                array(42, 43, 44, null, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(42, 43, 44, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                12,
            ),
            array(
                array(42, 43, 44, null, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(42, 43, 44, null, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                12,
                0,
                200,
                ResultSet::INCLUDE_INVALID_ITEMS,
            ),
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(48, 49, 50, 51, 52),
                13,
                6,
                5,
            ),
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, null, 51, 52, 53, 54),
                array(48, 49, 51, 52, 53),
                12,
                6,
                5,
            ),
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, null, 51, 52, 53, 54),
                array(48, 49, null, 51, 52, 53),
                12,
                6,
                5,
                ResultSet::INCLUDE_INVALID_ITEMS,
            ),
            array(
                array(42, null, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(49, 50, 51, 52, 53),
                12,
                6,
                5,
            ),
            array(
                array(42, null, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(49, 50, 51, 52, 53),
                12,
                6,
                5,
                ResultSet::INCLUDE_INVALID_ITEMS,
            ),
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, null, 54),
                array(48, 49, 50, 51, 52),
                12,
                6,
                5,
            ),
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, null, 54),
                array(48, 49, 50, 51, 52),
                12,
                6,
                5,
                ResultSet::INCLUDE_INVALID_ITEMS,
            ),
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(48, 49, 50, 51, 52, 53, 54),
                13,
                6,
            ),
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, null, 51, 52, 53, 54),
                array(48, 49, 51, 52, 53, 54),
                12,
                6,
            ),
            array(
                array(42, 43, 44, 45, 46, 47, 48, 49, null, 51, 52, 53, 54),
                array(48, 49, null, 51, 52, 53, 54),
                12,
                6,
                200,
                ResultSet::INCLUDE_INVALID_ITEMS,
            ),
            array(
                array(42, null, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(49, 50, 51, 52, 53, 54),
                12,
                6,
            ),
            array(
                array(42, null, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54),
                array(49, 50, 51, 52, 53, 54),
                12,
                6,
                200,
                ResultSet::INCLUDE_INVALID_ITEMS,
            ),
            array(
                array(),
                array(),
                0,
            ),
            array(
                array(),
                array(),
                0,
                5,
            ),
        );
    }

    private function buildExpectedValues(array $values)
    {
        $results = array();
        foreach ($values as $key => $value) {
            $results[] = new Result($key, new Item(array('value' => $value)));
        }

        return $results;
    }
}
