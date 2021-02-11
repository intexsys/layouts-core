<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Persistence\Doctrine\Handler;

use Netgen\Layouts\Exception\BadStateException;
use Netgen\Layouts\Exception\NotFoundException;
use Netgen\Layouts\Persistence\Values\Layout\Layout;
use Netgen\Layouts\Persistence\Values\Layout\LayoutCopyStruct;
use Netgen\Layouts\Persistence\Values\Layout\LayoutCreateStruct;
use Netgen\Layouts\Persistence\Values\Layout\LayoutUpdateStruct;
use Netgen\Layouts\Persistence\Values\Layout\Zone;
use Netgen\Layouts\Persistence\Values\Layout\ZoneCreateStruct;
use Netgen\Layouts\Persistence\Values\Layout\ZoneUpdateStruct;
use Netgen\Layouts\Persistence\Values\Value;
use Netgen\Layouts\Tests\Persistence\Doctrine\TestCaseTrait;
use Netgen\Layouts\Tests\TestCase\ExportObjectTrait;
use Netgen\Layouts\Tests\TestCase\UuidGeneratorTrait;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class LayoutHandlerTest extends TestCase
{
    use ExportObjectTrait;
    use TestCaseTrait;
    use UuidGeneratorTrait;

    /**
     * @var \Netgen\Layouts\Persistence\Handler\LayoutHandlerInterface
     */
    private $layoutHandler;

    /**
     * @var \Netgen\Layouts\Persistence\Handler\BlockHandlerInterface
     */
    private $blockHandler;

    /**
     * @var \Netgen\Layouts\Persistence\Handler\CollectionHandlerInterface
     */
    private $collectionHandler;

    protected function setUp(): void
    {
        $this->createDatabase();

        $this->layoutHandler = $this->createLayoutHandler();
        $this->blockHandler = $this->createBlockHandler();
        $this->collectionHandler = $this->createCollectionHandler();
    }

    /**
     * Tears down the tests.
     */
    protected function tearDown(): void
    {
        $this->closeDatabase();
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::__construct
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::__construct
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutSelectQuery
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutData
     */
    public function testLoadLayout(): void
    {
        $layout = $this->layoutHandler->loadLayout(1, Value::STATUS_PUBLISHED);

        self::assertSame(
            [
                'id' => 1,
                'uuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'type' => '4_zones_a',
                'name' => 'My layout',
                'description' => 'My layout description',
                'shared' => false,
                'created' => 1447065813,
                'modified' => 1447065813,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'status' => Value::STATUS_PUBLISHED,
            ],
            $this->exportObject($layout)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutData
     */
    public function testLoadLayoutThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Could not find layout with identifier "999999"');

        $this->layoutHandler->loadLayout(999999, Value::STATUS_PUBLISHED);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadZone
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getZoneSelectQuery
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadZoneData
     */
    public function testLoadZone(): void
    {
        $zone = $this->layoutHandler->loadZone(2, Value::STATUS_PUBLISHED, 'top');

        self::assertSame(
            [
                'identifier' => 'top',
                'layoutId' => 2,
                'layoutUuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                'rootBlockId' => 5,
                'linkedLayoutUuid' => 'd8e55af7-cf62-5f28-ae15-331b457d82e9',
                'linkedZoneIdentifier' => 'top',
                'status' => Value::STATUS_PUBLISHED,
            ],
            $this->exportObject($zone)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadZone
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadZoneData
     */
    public function testLoadZoneThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Could not find zone with identifier "non_existing"');

        $this->layoutHandler->loadZone(1, Value::STATUS_PUBLISHED, 'non_existing');
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadLayouts
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutSelectQuery
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutIds
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutsData
     */
    public function testLoadLayouts(): void
    {
        $layouts = $this->layoutHandler->loadLayouts();

        self::assertContainsOnlyInstancesOf(Layout::class, $layouts);

        self::assertSame(
            [
                [
                    'id' => 1,
                    'uuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'type' => '4_zones_a',
                    'name' => 'My layout',
                    'description' => 'My layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 2,
                    'uuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                    'type' => '4_zones_b',
                    'name' => 'My other layout',
                    'description' => 'My other layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 6,
                    'uuid' => '7900306c-0351-5f0a-9b33-5d4f5a1f3943',
                    'type' => '4_zones_b',
                    'name' => 'My sixth layout',
                    'description' => 'My sixth layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList($layouts)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadLayouts
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutSelectQuery
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutIds
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutsData
     */
    public function testLoadLayoutsWithUnpublishedLayouts(): void
    {
        $layouts = $this->layoutHandler->loadLayouts(true);

        self::assertContainsOnlyInstancesOf(Layout::class, $layouts);

        self::assertSame(
            [
                [
                    'id' => 4,
                    'uuid' => '8626a1ca-6413-5f54-acef-de7db06272ce',
                    'type' => '4_zones_b',
                    'name' => 'My fourth layout',
                    'description' => 'My fourth layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_DRAFT,
                ],
                [
                    'id' => 1,
                    'uuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'type' => '4_zones_a',
                    'name' => 'My layout',
                    'description' => 'My layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 2,
                    'uuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                    'type' => '4_zones_b',
                    'name' => 'My other layout',
                    'description' => 'My other layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 7,
                    'uuid' => '4b0202b3-5d06-5962-ae0c-bbeb25ee3503',
                    'type' => '4_zones_b',
                    'name' => 'My seventh layout',
                    'description' => 'My seventh layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_DRAFT,
                ],
                [
                    'id' => 6,
                    'uuid' => '7900306c-0351-5f0a-9b33-5d4f5a1f3943',
                    'type' => '4_zones_b',
                    'name' => 'My sixth layout',
                    'description' => 'My sixth layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList($layouts)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadLayouts
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutSelectQuery
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutIds
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutsData
     */
    public function testLoadLayoutsAndOffsetAndLimit(): void
    {
        $layouts = $this->layoutHandler->loadLayouts(false, 0, 2);

        self::assertContainsOnlyInstancesOf(Layout::class, $layouts);

        self::assertSame(
            [
                [
                    'id' => 1,
                    'uuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'type' => '4_zones_a',
                    'name' => 'My layout',
                    'description' => 'My layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 2,
                    'uuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                    'type' => '4_zones_b',
                    'name' => 'My other layout',
                    'description' => 'My other layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList($layouts)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadLayouts
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutSelectQuery
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutIds
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutsData
     */
    public function testLoadLayoutsWithUnpublishedLayoutsAndOffsetAndLimit(): void
    {
        $layouts = $this->layoutHandler->loadLayouts(true, 0, 3);

        self::assertContainsOnlyInstancesOf(Layout::class, $layouts);

        self::assertSame(
            [
                [
                    'id' => 4,
                    'uuid' => '8626a1ca-6413-5f54-acef-de7db06272ce',
                    'type' => '4_zones_b',
                    'name' => 'My fourth layout',
                    'description' => 'My fourth layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_DRAFT,
                ],
                [
                    'id' => 1,
                    'uuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'type' => '4_zones_a',
                    'name' => 'My layout',
                    'description' => 'My layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 2,
                    'uuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                    'type' => '4_zones_b',
                    'name' => 'My other layout',
                    'description' => 'My other layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList($layouts)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::getLayoutsCount
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutsCount
     */
    public function testGetLayoutsCount(): void
    {
        self::assertSame(3, $this->layoutHandler->getLayoutsCount());
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::getLayoutsCount
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutsCount
     */
    public function testGetLayoutsCountWithUnpublishedLayouts(): void
    {
        self::assertSame(5, $this->layoutHandler->getLayoutsCount(true));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadSharedLayouts
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutSelectQuery
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutIds
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutsData
     */
    public function testLoadSharedLayouts(): void
    {
        $layouts = $this->layoutHandler->loadSharedLayouts();

        self::assertContainsOnlyInstancesOf(Layout::class, $layouts);

        self::assertSame(
            [
                [
                    'id' => 5,
                    'uuid' => '399ad9ac-777a-50ba-945a-06e9f57add12',
                    'type' => '4_zones_b',
                    'name' => 'My fifth layout',
                    'description' => 'My fifth layout description',
                    'shared' => true,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 3,
                    'uuid' => 'd8e55af7-cf62-5f28-ae15-331b457d82e9',
                    'type' => '4_zones_b',
                    'name' => 'My third layout',
                    'description' => 'My third layout description',
                    'shared' => true,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList($layouts)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::getSharedLayoutsCount
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutsCount
     */
    public function testGetSharedLayoutsCount(): void
    {
        self::assertSame(2, $this->layoutHandler->getSharedLayoutsCount());
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadAllLayouts
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutSelectQuery
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutIds
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutsData
     */
    public function testLoadAllLayouts(): void
    {
        $layouts = $this->layoutHandler->loadAllLayouts();

        self::assertContainsOnlyInstancesOf(Layout::class, $layouts);

        self::assertSame(
            [
                [
                    'id' => 5,
                    'uuid' => '399ad9ac-777a-50ba-945a-06e9f57add12',
                    'type' => '4_zones_b',
                    'name' => 'My fifth layout',
                    'description' => 'My fifth layout description',
                    'shared' => true,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 1,
                    'uuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'type' => '4_zones_a',
                    'name' => 'My layout',
                    'description' => 'My layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 2,
                    'uuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                    'type' => '4_zones_b',
                    'name' => 'My other layout',
                    'description' => 'My other layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 6,
                    'uuid' => '7900306c-0351-5f0a-9b33-5d4f5a1f3943',
                    'type' => '4_zones_b',
                    'name' => 'My sixth layout',
                    'description' => 'My sixth layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 3,
                    'uuid' => 'd8e55af7-cf62-5f28-ae15-331b457d82e9',
                    'type' => '4_zones_b',
                    'name' => 'My third layout',
                    'description' => 'My third layout description',
                    'shared' => true,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList($layouts)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::getAllLayoutsCount
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutsCount
     */
    public function testGetAllLayoutsCount(): void
    {
        self::assertSame(5, $this->layoutHandler->getAllLayoutsCount());
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadRelatedLayouts
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getLayoutSelectQuery
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadRelatedLayoutsData
     */
    public function testLoadRelatedLayouts(): void
    {
        $layouts = $this->layoutHandler->loadRelatedLayouts(
            $this->layoutHandler->loadLayout(3, Value::STATUS_PUBLISHED)
        );

        self::assertContainsOnlyInstancesOf(Layout::class, $layouts);

        self::assertSame(
            [
                [
                    'id' => 2,
                    'uuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                    'type' => '4_zones_b',
                    'name' => 'My other layout',
                    'description' => 'My other layout description',
                    'shared' => false,
                    'created' => 1447065813,
                    'modified' => 1447065813,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList($layouts)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::getRelatedLayoutsCount
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::getRelatedLayoutsCount
     */
    public function testGetRelatedLayoutsCount(): void
    {
        $count = $this->layoutHandler->getRelatedLayoutsCount(
            $this->layoutHandler->loadLayout(3, Value::STATUS_PUBLISHED)
        );

        self::assertSame(1, $count);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::layoutExists
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::layoutExists
     */
    public function testLayoutExists(): void
    {
        self::assertTrue($this->layoutHandler->layoutExists(1, Value::STATUS_PUBLISHED));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::layoutExists
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::layoutExists
     */
    public function testLayoutNotExists(): void
    {
        self::assertFalse($this->layoutHandler->layoutExists(999999, Value::STATUS_PUBLISHED));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::layoutExists
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::layoutExists
     */
    public function testLayoutNotExistsInStatus(): void
    {
        self::assertFalse($this->layoutHandler->layoutExists(1, Value::STATUS_ARCHIVED));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::layoutNameExists
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::layoutNameExists
     */
    public function testLayoutNameExists(): void
    {
        self::assertTrue($this->layoutHandler->layoutNameExists('My layout'));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::layoutNameExists
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::layoutNameExists
     */
    public function testLayoutNameNotExists(): void
    {
        self::assertFalse($this->layoutHandler->layoutNameExists('Non existent'));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::layoutNameExists
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::layoutNameExists
     */
    public function testLayoutNameNotExistsWithExcludedId(): void
    {
        self::assertFalse($this->layoutHandler->layoutNameExists('My layout', 1));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::layoutNameExists
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::layoutNameExists
     */
    public function testLayoutNameNotExistsWithExcludedUuid(): void
    {
        self::assertFalse($this->layoutHandler->layoutNameExists('My layout', Uuid::fromString('81168ed3-86f9-55ea-b153-101f96f2c136')));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::loadLayoutZones
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::loadLayoutZonesData
     */
    public function testLoadLayoutZones(): void
    {
        $zones = $this->layoutHandler->loadLayoutZones(
            $this->layoutHandler->loadLayout(2, Value::STATUS_PUBLISHED)
        );

        self::assertContainsOnlyInstancesOf(Zone::class, $zones);

        self::assertSame(
            [
                'bottom' => [
                    'identifier' => 'bottom',
                    'layoutId' => 2,
                    'layoutUuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                    'rootBlockId' => 8,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_PUBLISHED,
                ],
                'left' => [
                    'identifier' => 'left',
                    'layoutId' => 2,
                    'layoutUuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                    'rootBlockId' => 6,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_PUBLISHED,
                ],
                'right' => [
                    'identifier' => 'right',
                    'layoutId' => 2,
                    'layoutUuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                    'rootBlockId' => 7,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_PUBLISHED,
                ],
                'top' => [
                    'identifier' => 'top',
                    'layoutId' => 2,
                    'layoutUuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                    'rootBlockId' => 5,
                    'linkedLayoutUuid' => 'd8e55af7-cf62-5f28-ae15-331b457d82e9',
                    'linkedZoneIdentifier' => 'top',
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList($zones)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::updateZone
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::updateZone
     */
    public function testUpdateZone(): void
    {
        $zone = $this->layoutHandler->loadZone(1, Value::STATUS_DRAFT, 'top');
        $linkedZone = $this->layoutHandler->loadZone(3, Value::STATUS_PUBLISHED, 'top');

        $updatedZone = $this->layoutHandler->updateZone(
            $zone,
            ZoneUpdateStruct::fromArray(
                [
                    'linkedZone' => $linkedZone,
                ]
            )
        );

        self::assertSame(
            [
                'identifier' => 'top',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'rootBlockId' => 1,
                'linkedLayoutUuid' => 'd8e55af7-cf62-5f28-ae15-331b457d82e9',
                'linkedZoneIdentifier' => 'top',
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($updatedZone)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::updateZone
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::updateZone
     */
    public function testUpdateZoneWithResettingLinkedZone(): void
    {
        $zone = $this->layoutHandler->loadZone(1, Value::STATUS_DRAFT, 'left');

        $updatedZone = $this->layoutHandler->updateZone(
            $zone,
            ZoneUpdateStruct::fromArray(
                [
                    'linkedZone' => false,
                ]
            )
        );

        self::assertSame(
            [
                'identifier' => 'left',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'rootBlockId' => 2,
                'linkedLayoutUuid' => null,
                'linkedZoneIdentifier' => null,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($updatedZone)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::createLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createLayoutTranslation
     */
    public function testCreateLayout(): void
    {
        $layoutCreateStruct = new LayoutCreateStruct();
        $layoutCreateStruct->uuid = null;
        $layoutCreateStruct->type = 'new_layout';
        $layoutCreateStruct->name = 'New layout';
        $layoutCreateStruct->description = 'New description';
        $layoutCreateStruct->shared = true;
        $layoutCreateStruct->status = Value::STATUS_DRAFT;
        $layoutCreateStruct->mainLocale = 'en';

        $createdLayout = $this->withUuids(
            function () use ($layoutCreateStruct): Layout {
                return $this->layoutHandler->createLayout($layoutCreateStruct);
            },
            ['f06f245a-f951-52c8-bfa3-84c80154eadc']
        );

        self::assertSame(8, $createdLayout->id);
        self::assertSame('f06f245a-f951-52c8-bfa3-84c80154eadc', $createdLayout->uuid);
        self::assertSame('new_layout', $createdLayout->type);
        self::assertSame('New layout', $createdLayout->name);
        self::assertSame('New description', $createdLayout->description);
        self::assertSame(Value::STATUS_DRAFT, $createdLayout->status);
        self::assertTrue($createdLayout->shared);
        self::assertSame('en', $createdLayout->mainLocale);
        self::assertGreaterThan(0, $createdLayout->created);
        self::assertGreaterThan(0, $createdLayout->modified);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::createLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createLayoutTranslation
     */
    public function testCreateLayoutWithCustomUuid(): void
    {
        $layoutCreateStruct = new LayoutCreateStruct();
        $layoutCreateStruct->uuid = '5f35d4d3-8fa7-4602-9d4c-c74c2b16e3d7';
        $layoutCreateStruct->type = 'new_layout';
        $layoutCreateStruct->name = 'New layout';
        $layoutCreateStruct->description = 'New description';
        $layoutCreateStruct->shared = true;
        $layoutCreateStruct->status = Value::STATUS_DRAFT;
        $layoutCreateStruct->mainLocale = 'en';

        $createdLayout = $this->layoutHandler->createLayout($layoutCreateStruct);

        self::assertSame(8, $createdLayout->id);
        self::assertSame('5f35d4d3-8fa7-4602-9d4c-c74c2b16e3d7', $createdLayout->uuid);
        self::assertSame('new_layout', $createdLayout->type);
        self::assertSame('New layout', $createdLayout->name);
        self::assertSame('New description', $createdLayout->description);
        self::assertSame(Value::STATUS_DRAFT, $createdLayout->status);
        self::assertTrue($createdLayout->shared);
        self::assertSame('en', $createdLayout->mainLocale);
        self::assertGreaterThan(0, $createdLayout->created);
        self::assertGreaterThan(0, $createdLayout->modified);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::createLayout
     */
    public function testCreateLayoutWithExistingCustomUuidThrowsBadStateException(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "uuid" has an invalid state. Layout with provided UUID already exists.');

        $layoutCreateStruct = new LayoutCreateStruct();
        $layoutCreateStruct->uuid = '81168ed3-86f9-55ea-b153-101f96f2c136';
        $layoutCreateStruct->type = 'new_layout';
        $layoutCreateStruct->name = 'New layout';
        $layoutCreateStruct->description = 'New description';
        $layoutCreateStruct->shared = true;
        $layoutCreateStruct->status = Value::STATUS_DRAFT;
        $layoutCreateStruct->mainLocale = 'en';

        $this->layoutHandler->createLayout($layoutCreateStruct);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::createLayoutTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createLayoutTranslation
     */
    public function testCreateLayoutTranslation(): void
    {
        $originalLayout = $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT);
        $layout = $this->layoutHandler->createLayoutTranslation($originalLayout, 'de', 'en');

        self::assertSame('en', $layout->mainLocale);
        self::assertSame(['en', 'hr', 'de'], $layout->availableLocales);
        self::assertSame($originalLayout->created, $layout->created);
        self::assertGreaterThan($originalLayout->modified, $layout->modified);

        $layoutBlocks = $this->blockHandler->loadLayoutBlocks($layout);
        foreach ($layoutBlocks as $layoutBlock) {
            $layoutBlock->isTranslatable ?
                self::assertContains('de', $layoutBlock->availableLocales) :
                self::assertNotContains('de', $layoutBlock->availableLocales);
        }
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::createLayoutTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createLayoutTranslation
     */
    public function testCreateLayoutTranslationThrowsBadStateExceptionWithExistingLocale(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "locale" has an invalid state. Layout already has the provided locale.');

        $this->layoutHandler->createLayoutTranslation(
            $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT),
            'en',
            'hr'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::createLayoutTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createLayoutTranslation
     */
    public function testCreateLayoutTranslationThrowsBadStateExceptionWithNonExistingSourceLocale(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "sourceLocale" has an invalid state. Layout does not have the provided source locale.');

        $this->layoutHandler->createLayoutTranslation(
            $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT),
            'de',
            'fr'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::setMainTranslation
     */
    public function testSetMainTranslation(): void
    {
        $layout = $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT);
        $updatedLayout = $this->layoutHandler->setMainTranslation($layout, 'hr');

        self::assertSame('hr', $updatedLayout->mainLocale);
        self::assertSame($layout->created, $updatedLayout->created);
        self::assertGreaterThan($layout->modified, $updatedLayout->modified);

        $layoutBlocks = $this->blockHandler->loadLayoutBlocks($updatedLayout);
        foreach ($layoutBlocks as $layoutBlock) {
            self::assertSame('hr', $layoutBlock->mainLocale);
        }
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::setMainTranslation
     */
    public function testSetMainTranslationThrowsBadStateExceptionWithNonExistingLocale(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "mainLocale" has an invalid state. Layout does not have the provided locale.');

        $layout = $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT);
        $this->layoutHandler->setMainTranslation($layout, 'de');
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::createZone
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createZone
     */
    public function testCreateZone(): void
    {
        $linkedZone = $this->layoutHandler->loadZone(3, Value::STATUS_PUBLISHED, 'left');

        $zoneCreateStruct = new ZoneCreateStruct();
        $zoneCreateStruct->identifier = 'new_zone';
        $zoneCreateStruct->linkedZone = $linkedZone;

        /** @var \Netgen\Layouts\Persistence\Values\Layout\Zone $createdZone */
        $createdZone = $this->withUuids(
            function () use ($zoneCreateStruct): Zone {
                return $this->layoutHandler->createZone(
                    $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT),
                    $zoneCreateStruct
                );
            },
            ['f06f245a-f951-52c8-bfa3-84c80154eadc']
        );

        self::assertSame(1, $createdZone->layoutId);
        self::assertSame('81168ed3-86f9-55ea-b153-101f96f2c136', $createdZone->layoutUuid);
        self::assertSame(Value::STATUS_DRAFT, $createdZone->status);
        self::assertSame('new_zone', $createdZone->identifier);
        self::assertSame(39, $createdZone->rootBlockId);
        self::assertSame('d8e55af7-cf62-5f28-ae15-331b457d82e9', $createdZone->linkedLayoutUuid);
        self::assertSame('left', $createdZone->linkedZoneIdentifier);

        $rootBlock = $this->blockHandler->loadBlock(39, Value::STATUS_DRAFT);

        self::assertSame(
            [
                'id' => 39,
                'uuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'layoutId' => $createdZone->layoutId,
                'layoutUuid' => $createdZone->layoutUuid,
                'depth' => 0,
                'path' => '/39/',
                'parentId' => null,
                'parentUuid' => null,
                'placeholder' => null,
                'position' => null,
                'definitionIdentifier' => '',
                'parameters' => ['en' => []],
                'config' => [],
                'viewType' => '',
                'itemViewType' => '',
                'name' => '',
                'isTranslatable' => false,
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($rootBlock)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::updateLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::updateLayout
     */
    public function testUpdateLayout(): void
    {
        $layoutUpdateStruct = new LayoutUpdateStruct();
        $layoutUpdateStruct->name = 'New name';
        $layoutUpdateStruct->modified = 123;
        $layoutUpdateStruct->description = 'New description';

        $originalLayout = $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT);
        $updatedLayout = $this->layoutHandler->updateLayout(
            $originalLayout,
            $layoutUpdateStruct
        );

        self::assertSame('New name', $updatedLayout->name);
        self::assertSame('New description', $updatedLayout->description);
        self::assertSame($originalLayout->created, $updatedLayout->created);
        self::assertSame(123, $updatedLayout->modified);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::updateLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::updateLayout
     */
    public function testUpdateLayoutWithDefaultValues(): void
    {
        $layoutUpdateStruct = new LayoutUpdateStruct();

        $originalLayout = $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT);
        $updatedLayout = $this->layoutHandler->updateLayout(
            $originalLayout,
            $layoutUpdateStruct
        );

        self::assertSame('My layout', $updatedLayout->name);
        self::assertSame('My layout description', $updatedLayout->description);
        self::assertSame($originalLayout->created, $updatedLayout->created);
        self::assertGreaterThan($originalLayout->modified, $updatedLayout->modified);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::copyLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::createZone
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createLayoutTranslation
     */
    public function testCopyLayout(): void
    {
        // Link the zone before copying, to make sure those are copied too
        $this->layoutHandler->updateZone(
            $this->layoutHandler->loadZone(1, Value::STATUS_PUBLISHED, 'left'),
            ZoneUpdateStruct::fromArray(
                [
                    'linkedZone' => $this->layoutHandler->loadZone(3, Value::STATUS_PUBLISHED, 'left'),
                ]
            )
        );

        $copyStruct = new LayoutCopyStruct();
        $copyStruct->name = 'New name';
        $copyStruct->description = 'New description';

        $originalLayout = $this->layoutHandler->loadLayout(1, Value::STATUS_PUBLISHED);

        /** @var \Netgen\Layouts\Persistence\Values\Layout\Layout $copiedLayout */
        $copiedLayout = $this->withUuids(
            function () use ($originalLayout, $copyStruct): Layout {
                return $this->layoutHandler->copyLayout($originalLayout, $copyStruct);
            },
            [
                'b90ece3f-9520-54e8-8f43-e625051df284',
                'efd1d54a-5d53-518f-91a5-f4965c242a67',
                '1169074c-8779-5b64-afec-c910705e418a',
                'aaa3659b-b574-5e6b-8902-0ea37f576469',
                '8abc6a32-d8a7-5c30-afa5-9a9efa99b6ae',
                '6f76b761-0dea-55cd-b963-ff4c0cc2184d',
                'dac29092-e4cb-588b-bad5-a6633eee3b74',
                '7fd3bef4-6ed0-561d-ac32-4ca0ead7ee03',
                'cf29cf92-0294-5581-abdb-58d11978186b',
                '805895b2-6292-5243-a0c0-06a6ec0e28a2',
                '232f094f-7ba6-52ea-983f-7237ab95c7d0',
                '019a0bdc-19fb-559d-81c0-11ddb9ec3f9f',
                '589b39c3-ffbd-5c2f-9cef-85b8d01437a8',
                '39c520ff-d5c1-545c-898c-afd4ec693c82',
                '96ee48a2-b2c1-53ac-9e00-d42ae41f9833',
                'c86643b1-0486-573b-b8d9-0b0c2a623d31',
                'f08717e5-5910-574d-b976-03d877c4729b',
                'e804ebd6-dc99-53bb-85d5-196d68933761',
                '910f4fe2-97b0-5599-8a45-8fb8a8e0ca6d',
                '76b05000-33ac-53f7-adfd-c91936d1f6b1',
                '6dc13cc7-fd76-5e41-8b0c-1ed93ece7fcf',
                '70fe4f3a-7e9d-5a1f-9e6a-b038c06ea117',
                '3a3aa59a-76fe-532f-8a03-c04a93d803f6',
                '8634280c-f498-416e-b4a7-0b0bd0869c85',
                '63326bc3-baee-49c9-82e7-7b2a9aca081a',
                '3a17132d-9072-45f3-a0b3-b91bd4b0fcf3',
                '29f091e0-81cc-4bd3-aec5-673cd06abce5',
            ]
        );

        self::assertSame(8, $copiedLayout->id);
        self::assertSame('b90ece3f-9520-54e8-8f43-e625051df284', $copiedLayout->uuid);
        self::assertSame('4_zones_a', $copiedLayout->type);
        self::assertSame('New name', $copiedLayout->name);
        self::assertSame('New description', $copiedLayout->description);
        self::assertSame(Value::STATUS_PUBLISHED, $copiedLayout->status);
        self::assertFalse($copiedLayout->shared);
        self::assertSame('en', $copiedLayout->mainLocale);
        self::assertSame(['en', 'hr'], $copiedLayout->availableLocales);

        self::assertGreaterThan($originalLayout->created, $copiedLayout->created);
        self::assertSame($copiedLayout->created, $copiedLayout->modified);

        self::assertSame(
            [
                'bottom' => [
                    'identifier' => 'bottom',
                    'layoutId' => $copiedLayout->id,
                    'layoutUuid' => $copiedLayout->uuid,
                    'rootBlockId' => 39,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_PUBLISHED,
                ],
                'left' => [
                    'identifier' => 'left',
                    'layoutId' => $copiedLayout->id,
                    'layoutUuid' => $copiedLayout->uuid,
                    'rootBlockId' => 40,
                    'linkedLayoutUuid' => 'd8e55af7-cf62-5f28-ae15-331b457d82e9',
                    'linkedZoneIdentifier' => 'left',
                    'status' => Value::STATUS_PUBLISHED,
                ],
                'right' => [
                    'identifier' => 'right',
                    'layoutId' => $copiedLayout->id,
                    'layoutUuid' => $copiedLayout->uuid,
                    'rootBlockId' => 42,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_PUBLISHED,
                ],
                'top' => [
                    'identifier' => 'top',
                    'layoutId' => $copiedLayout->id,
                    'layoutUuid' => $copiedLayout->uuid,
                    'rootBlockId' => 45,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList(
                $this->layoutHandler->loadLayoutZones($copiedLayout)
            )
        );

        $rootBlock = $this->blockHandler->loadBlock(40, Value::STATUS_PUBLISHED);

        self::assertSame(
            [
                [
                    'id' => 41,
                    'uuid' => 'aaa3659b-b574-5e6b-8902-0ea37f576469',
                    'layoutId' => $copiedLayout->id,
                    'layoutUuid' => $copiedLayout->uuid,
                    'depth' => 1,
                    'path' => '/40/41/',
                    'parentId' => $rootBlock->id,
                    'parentUuid' => $rootBlock->uuid,
                    'placeholder' => 'root',
                    'position' => 0,
                    'definitionIdentifier' => 'list',
                    'parameters' => [
                        'en' => [
                            'number_of_columns' => 3,
                        ],
                        'hr' => [
                            'number_of_columns' => 3,
                        ],
                    ],
                    'config' => [
                        'key' => [
                            'param1' => false,
                        ],
                    ],
                    'viewType' => 'grid',
                    'itemViewType' => 'standard',
                    'name' => 'My other block',
                    'isTranslatable' => true,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList(
                $this->blockHandler->loadChildBlocks($rootBlock)
            )
        );

        $rootBlock = $this->blockHandler->loadBlock(42, Value::STATUS_PUBLISHED);

        self::assertSame(
            [
                [
                    'id' => 43,
                    'uuid' => '6f76b761-0dea-55cd-b963-ff4c0cc2184d',
                    'layoutId' => $copiedLayout->id,
                    'layoutUuid' => $copiedLayout->uuid,
                    'depth' => 1,
                    'path' => '/42/43/',
                    'parentId' => $rootBlock->id,
                    'parentUuid' => $rootBlock->uuid,
                    'placeholder' => 'root',
                    'position' => 0,
                    'definitionIdentifier' => 'list',
                    'parameters' => [
                        'en' => [
                            'number_of_columns' => 3,
                        ],
                        'hr' => [
                            'number_of_columns' => 3,
                        ],
                    ],
                    'config' => [],
                    'viewType' => 'grid',
                    'itemViewType' => 'standard_with_intro',
                    'name' => 'My published block',
                    'isTranslatable' => true,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 44,
                    'uuid' => '6dc13cc7-fd76-5e41-8b0c-1ed93ece7fcf',
                    'layoutId' => $copiedLayout->id,
                    'layoutUuid' => $copiedLayout->uuid,
                    'depth' => 1,
                    'path' => '/42/44/',
                    'parentId' => $rootBlock->id,
                    'parentUuid' => $rootBlock->uuid,
                    'placeholder' => 'root',
                    'position' => 1,
                    'definitionIdentifier' => 'list',
                    'parameters' => [
                        'en' => [
                            'number_of_columns' => 3,
                        ],
                    ],
                    'config' => [],
                    'viewType' => 'grid',
                    'itemViewType' => 'standard',
                    'name' => 'My fourth block',
                    'isTranslatable' => false,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList(
                $this->blockHandler->loadChildBlocks($rootBlock)
            )
        );

        // Verify that collections were copied
        $this->collectionHandler->loadCollection(7, Value::STATUS_PUBLISHED);
        $this->collectionHandler->loadCollection(8, Value::STATUS_PUBLISHED);

        // Verify the state of the collection references

        // First block
        $references = $this->collectionHandler->loadCollectionReferences(
            $this->blockHandler->loadBlock(41, Value::STATUS_PUBLISHED)
        );

        self::assertCount(0, $references);

        // Second block
        $references = $this->collectionHandler->loadCollectionReferences(
            $this->blockHandler->loadBlock(43, Value::STATUS_PUBLISHED)
        );

        self::assertCount(2, $references);
        self::assertContains($references[0]->collectionId, [7, 8]);
        self::assertContains($references[1]->collectionId, [7, 8]);

        // Third block
        $references = $this->collectionHandler->loadCollectionReferences(
            $this->blockHandler->loadBlock(44, Value::STATUS_PUBLISHED)
        );

        self::assertCount(1, $references);
        self::assertSame($references[0]->collectionId, 9);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::changeLayoutType
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createZone
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::deleteZone
     */
    public function testChangeLayoutType(): void
    {
        // Link the zone before copying, to make sure those are removed
        $this->layoutHandler->updateZone(
            $this->layoutHandler->loadZone(1, Value::STATUS_DRAFT, 'left'),
            ZoneUpdateStruct::fromArray(
                [
                    'linkedZone' => $this->layoutHandler->loadZone(3, Value::STATUS_PUBLISHED, 'left'),
                ]
            )
        );

        $originalLayout = $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT);
        $updatedLayout = $this->layoutHandler->changeLayoutType(
            $originalLayout,
            '4_zones_b',
            [
                'top' => ['left', 'right'],
                'left' => [],
                'right' => [],
                'bottom' => [],
            ]
        );

        self::assertSame(1, $updatedLayout->id);
        self::assertSame('81168ed3-86f9-55ea-b153-101f96f2c136', $updatedLayout->uuid);
        self::assertSame('4_zones_b', $updatedLayout->type);
        self::assertSame('My layout', $updatedLayout->name);
        self::assertSame('My layout description', $updatedLayout->description);
        self::assertSame(Value::STATUS_DRAFT, $updatedLayout->status);
        self::assertFalse($updatedLayout->shared);

        self::assertSame($originalLayout->created, $updatedLayout->created);
        self::assertGreaterThan($originalLayout->modified, $updatedLayout->modified);

        self::assertSame(
            [
                'bottom' => [
                    'identifier' => 'bottom',
                    'layoutId' => $updatedLayout->id,
                    'layoutUuid' => $updatedLayout->uuid,
                    'rootBlockId' => 42,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_DRAFT,
                ],
                'left' => [
                    'identifier' => 'left',
                    'layoutId' => $updatedLayout->id,
                    'layoutUuid' => $updatedLayout->uuid,
                    'rootBlockId' => 40,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_DRAFT,
                ],
                'right' => [
                    'identifier' => 'right',
                    'layoutId' => $updatedLayout->id,
                    'layoutUuid' => $updatedLayout->uuid,
                    'rootBlockId' => 41,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_DRAFT,
                ],
                'top' => [
                    'identifier' => 'top',
                    'layoutId' => $updatedLayout->id,
                    'layoutUuid' => $updatedLayout->uuid,
                    'rootBlockId' => 39,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_DRAFT,
                ],
            ],
            $this->exportObjectList(
                $this->layoutHandler->loadLayoutZones($updatedLayout)
            )
        );

        $rootBlock = $this->blockHandler->loadBlock(39, Value::STATUS_DRAFT);

        self::assertSame(
            [
                [
                    'id' => 32,
                    'uuid' => 'b07d3a85-bcdb-5af2-9b6f-deba36c700e7',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'depth' => 1,
                    'path' => '/39/32/',
                    'parentId' => $rootBlock->id,
                    'parentUuid' => $rootBlock->uuid,
                    'placeholder' => 'root',
                    'position' => 0,
                    'definitionIdentifier' => 'list',
                    'parameters' => [
                        'en' => [
                            'number_of_columns' => 3,
                        ],
                        'hr' => [
                            'number_of_columns' => 3,
                        ],
                    ],
                    'config' => [
                        'key' => [
                            'param1' => false,
                        ],
                    ],
                    'viewType' => 'grid',
                    'itemViewType' => 'standard',
                    'name' => 'My other block',
                    'isTranslatable' => true,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_DRAFT,
                ],
                [
                    'id' => 31,
                    'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'depth' => 1,
                    'path' => '/39/31/',
                    'parentId' => $rootBlock->id,
                    'parentUuid' => $rootBlock->uuid,
                    'placeholder' => 'root',
                    'position' => 1,
                    'definitionIdentifier' => 'list',
                    'parameters' => [
                        'en' => [
                            'number_of_columns' => 2,
                            'css_class' => 'css-class',
                            'css_id' => 'css-id',
                        ],
                        'hr' => [
                            'css_class' => 'css-class-hr',
                            'css_id' => 'css-id',
                        ],
                    ],
                    'config' => [],
                    'viewType' => 'list',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => true,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_DRAFT,
                ],
                [
                    'id' => 35,
                    'uuid' => 'c2a30ea3-95ef-55b0-a584-fbcfd93cec9e',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'depth' => 1,
                    'path' => '/39/35/',
                    'parentId' => $rootBlock->id,
                    'parentUuid' => $rootBlock->uuid,
                    'placeholder' => 'root',
                    'position' => 2,
                    'definitionIdentifier' => 'list',
                    'parameters' => [
                        'en' => [
                            'number_of_columns' => 3,
                        ],
                    ],
                    'config' => [],
                    'viewType' => 'grid',
                    'itemViewType' => 'standard',
                    'name' => 'My fourth block',
                    'isTranslatable' => false,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_DRAFT,
                ],
            ],
            $this->exportObjectList(
                $this->blockHandler->loadChildBlocks($rootBlock)
            )
        );

        self::assertEmpty(
            $this->blockHandler->loadChildBlocks(
                $this->blockHandler->loadBlock(40, Value::STATUS_DRAFT)
            )
        );

        self::assertEmpty(
            $this->blockHandler->loadChildBlocks(
                $this->blockHandler->loadBlock(41, Value::STATUS_DRAFT)
            )
        );

        self::assertEmpty(
            $this->blockHandler->loadChildBlocks(
                $this->blockHandler->loadBlock(42, Value::STATUS_DRAFT)
            )
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::createLayoutStatus
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::createLayout
     */
    public function testCreateLayoutStatus(): void
    {
        // Link the zone before copying, to make sure those are copied too
        $this->layoutHandler->updateZone(
            $this->layoutHandler->loadZone(1, Value::STATUS_PUBLISHED, 'left'),
            ZoneUpdateStruct::fromArray(
                [
                    'linkedZone' => $this->layoutHandler->loadZone(3, Value::STATUS_PUBLISHED, 'left'),
                ]
            )
        );

        $originalLayout = $this->layoutHandler->loadLayout(1, Value::STATUS_PUBLISHED);
        $copiedLayout = $this->layoutHandler->createLayoutStatus(
            $originalLayout,
            Value::STATUS_ARCHIVED
        );

        self::assertSame(1, $copiedLayout->id);
        self::assertSame('81168ed3-86f9-55ea-b153-101f96f2c136', $copiedLayout->uuid);
        self::assertSame('4_zones_a', $copiedLayout->type);
        self::assertSame('My layout', $copiedLayout->name);
        self::assertSame('My layout description', $copiedLayout->description);
        self::assertSame(Value::STATUS_ARCHIVED, $copiedLayout->status);
        self::assertFalse($copiedLayout->shared);
        self::assertSame('en', $copiedLayout->mainLocale);
        self::assertSame(['en', 'hr'], $copiedLayout->availableLocales);

        self::assertSame($originalLayout->created, $copiedLayout->created);
        self::assertGreaterThan($originalLayout->modified, $copiedLayout->modified);

        self::assertSame(
            [
                'bottom' => [
                    'identifier' => 'bottom',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'rootBlockId' => 4,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_ARCHIVED,
                ],
                'left' => [
                    'identifier' => 'left',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'rootBlockId' => 2,
                    'linkedLayoutUuid' => 'd8e55af7-cf62-5f28-ae15-331b457d82e9',
                    'linkedZoneIdentifier' => 'left',
                    'status' => Value::STATUS_ARCHIVED,
                ],
                'right' => [
                    'identifier' => 'right',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'rootBlockId' => 3,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_ARCHIVED,
                ],
                'top' => [
                    'identifier' => 'top',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'rootBlockId' => 1,
                    'linkedLayoutUuid' => null,
                    'linkedZoneIdentifier' => null,
                    'status' => Value::STATUS_ARCHIVED,
                ],
            ],
            $this->exportObjectList(
                $this->layoutHandler->loadLayoutZones($copiedLayout)
            )
        );

        self::assertSame(
            [
                [
                    'id' => 32,
                    'uuid' => 'b07d3a85-bcdb-5af2-9b6f-deba36c700e7',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'depth' => 1,
                    'path' => '/2/32/',
                    'parentId' => 2,
                    'parentUuid' => '39d3ab66-1589-540f-95c4-6381acb4f010',
                    'placeholder' => 'root',
                    'position' => 0,
                    'definitionIdentifier' => 'list',
                    'parameters' => [
                        'en' => [
                            'number_of_columns' => 3,
                        ],
                        'hr' => [
                            'number_of_columns' => 3,
                        ],
                    ],
                    'config' => [
                        'key' => [
                            'param1' => false,
                        ],
                    ],
                    'viewType' => 'grid',
                    'itemViewType' => 'standard',
                    'name' => 'My other block',
                    'isTranslatable' => true,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_ARCHIVED,
                ],
            ],
            $this->exportObjectList(
                $this->blockHandler->loadChildBlocks(
                    $this->blockHandler->loadBlock(2, Value::STATUS_ARCHIVED)
                )
            )
        );

        self::assertSame(
            [
                [
                    'id' => 31,
                    'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'depth' => 1,
                    'path' => '/3/31/',
                    'parentId' => 3,
                    'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                    'placeholder' => 'root',
                    'position' => 0,
                    'definitionIdentifier' => 'list',
                    'parameters' => [
                        'en' => [
                            'number_of_columns' => 3,
                        ],
                        'hr' => [
                            'number_of_columns' => 3,
                        ],
                    ],
                    'config' => [],
                    'viewType' => 'grid',
                    'itemViewType' => 'standard_with_intro',
                    'name' => 'My published block',
                    'isTranslatable' => true,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_ARCHIVED,
                ],
                [
                    'id' => 35,
                    'uuid' => 'c2a30ea3-95ef-55b0-a584-fbcfd93cec9e',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'depth' => 1,
                    'path' => '/3/35/',
                    'parentId' => 3,
                    'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                    'placeholder' => 'root',
                    'position' => 1,
                    'definitionIdentifier' => 'list',
                    'parameters' => [
                        'en' => [
                            'number_of_columns' => 3,
                        ],
                    ],
                    'config' => [],
                    'viewType' => 'grid',
                    'itemViewType' => 'standard',
                    'name' => 'My fourth block',
                    'isTranslatable' => false,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_ARCHIVED,
                ],
            ],
            $this->exportObjectList(
                $this->blockHandler->loadChildBlocks(
                    $this->blockHandler->loadBlock(3, Value::STATUS_ARCHIVED)
                )
            )
        );

        // Verify that the collection status was copied
        $this->collectionHandler->loadCollection(2, Value::STATUS_ARCHIVED);

        // Verify the state of the collection references
        $archivedReferences = $this->collectionHandler->loadCollectionReferences(
            $this->blockHandler->loadBlock(31, Value::STATUS_ARCHIVED)
        );

        self::assertCount(2, $archivedReferences);
        self::assertContains($archivedReferences[0]->collectionId, [2, 3]);
        self::assertContains($archivedReferences[1]->collectionId, [2, 3]);

        // Second block
        $archivedReferences = $this->collectionHandler->loadCollectionReferences(
            $this->blockHandler->loadBlock(35, Value::STATUS_ARCHIVED)
        );

        self::assertCount(1, $archivedReferences);
        self::assertSame(4, $archivedReferences[0]->collectionId);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::deleteLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::deleteLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::deleteLayoutZones
     */
    public function testDeleteLayout(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Could not find layout with identifier "1"');

        $this->layoutHandler->deleteLayout(1);

        // Verify that we don't have the collections that were related to the layout
        try {
            $this->collectionHandler->loadCollection(1, Value::STATUS_DRAFT);
            $this->collectionHandler->loadCollection(2, Value::STATUS_PUBLISHED);
            $this->collectionHandler->loadCollection(3, Value::STATUS_PUBLISHED);
            self::fail('Collections not deleted after deleting the layout.');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        // Verify that we don't have the layout any more
        $this->layoutHandler->loadLayout(1, Value::STATUS_PUBLISHED);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::deleteLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::deleteLayout
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::deleteLayoutZones
     */
    public function testDeleteLayoutInOneStatus(): void
    {
        $this->layoutHandler->deleteLayout(1, Value::STATUS_DRAFT);

        // Verify that we don't have the layout in deleted status any more
        try {
            $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT);
            self::fail('Layout not deleted after deleting it in one status.');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        // Verify that NOT all layout statuses are deleted
        $this->layoutHandler->loadLayout(1, Value::STATUS_PUBLISHED);

        // Verify that we don't have the collection that was related to layout in deleted status any more
        try {
            $this->collectionHandler->loadCollection(1, Value::STATUS_DRAFT);
            self::fail('Collection not deleted after deleting layout in one status.');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        // Verify that NOT all collections are deleted
        $this->collectionHandler->loadCollection(2, Value::STATUS_PUBLISHED);
        $this->collectionHandler->loadCollection(3, Value::STATUS_PUBLISHED);
        $this->collectionHandler->loadCollection(4, Value::STATUS_PUBLISHED);

        // Verify the state of the collection references
        $publishedReferences = $this->collectionHandler->loadCollectionReferences(
            $this->blockHandler->loadBlock(31, Value::STATUS_PUBLISHED)
        );

        self::assertCount(2, $publishedReferences);
        self::assertContains($publishedReferences[0]->collectionId, [2, 3]);
        self::assertContains($publishedReferences[1]->collectionId, [2, 3]);

        // Second block
        $publishedReferences = $this->collectionHandler->loadCollectionReferences(
            $this->blockHandler->loadBlock(35, Value::STATUS_PUBLISHED)
        );

        self::assertCount(1, $publishedReferences);
        self::assertSame(4, $publishedReferences[0]->collectionId);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::deleteLayoutTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::updateLayoutModifiedDate
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::deleteLayoutTranslations
     */
    public function testDeleteLayoutTranslation(): void
    {
        $layout = $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT);
        $updatedLayout = $this->layoutHandler->deleteLayoutTranslation($layout, 'hr');

        self::assertSame($layout->created, $updatedLayout->created);
        self::assertGreaterThan($layout->modified, $updatedLayout->modified);

        self::assertSame('en', $updatedLayout->mainLocale);
        self::assertSame(['en'], $updatedLayout->availableLocales);

        $layoutBlocks = $this->blockHandler->loadLayoutBlocks($updatedLayout);
        foreach ($layoutBlocks as $layoutBlock) {
            self::assertNotContains('hr', $layoutBlock->availableLocales);
        }
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::deleteLayoutTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::updateLayoutModifiedDate
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::deleteLayoutTranslations
     */
    public function testDeleteLayoutTranslationWithInconsistentBlock(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Could not find block with identifier "31"');

        $layout = $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT);

        $block = $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);

        $block = $this->blockHandler->setMainTranslation($block, 'hr');
        $this->blockHandler->deleteBlockTranslation($block, 'en');

        $updatedLayout = $this->layoutHandler->deleteLayoutTranslation($layout, 'hr');

        self::assertSame($layout->created, $updatedLayout->created);
        self::assertGreaterThan($layout->modified, $updatedLayout->modified);

        self::assertSame('en', $updatedLayout->mainLocale);
        self::assertSame(['en'], $updatedLayout->availableLocales);

        $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::deleteLayoutTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::deleteLayoutTranslations
     */
    public function testDeleteLayoutTranslationWithNonExistingLocale(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "locale" has an invalid state. Layout does not have the provided locale.');

        $this->layoutHandler->deleteLayoutTranslation(
            $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT),
            'de'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\LayoutHandler::deleteLayoutTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\LayoutQueryHandler::deleteLayoutTranslations
     */
    public function testDeleteLayoutTranslationWithMainLocale(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "locale" has an invalid state. Main translation cannot be removed from the layout.');

        $this->layoutHandler->deleteLayoutTranslation(
            $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT),
            'en'
        );
    }
}
