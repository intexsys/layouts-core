<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Persistence\Doctrine\Mapper;

use Netgen\Layouts\Persistence\Doctrine\Mapper\LayoutMapper;
use Netgen\Layouts\Persistence\Values\Layout\Layout;
use Netgen\Layouts\Persistence\Values\Layout\Zone;
use Netgen\Layouts\Persistence\Values\Value;
use Netgen\Layouts\Tests\TestCase\ExportObjectTrait;
use PHPUnit\Framework\TestCase;

final class LayoutMapperTest extends TestCase
{
    use ExportObjectTrait;

    /**
     * @var \Netgen\Layouts\Persistence\Doctrine\Mapper\LayoutMapper
     */
    private $mapper;

    public function setUp(): void
    {
        $this->mapper = new LayoutMapper();
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Mapper\LayoutMapper::mapLayouts
     */
    public function testMapLayouts(): void
    {
        $data = [
            [
                'id' => '42',
                'type' => '4_zones_a',
                'name' => 'My layout',
                'description' => 'My layout description',
                'created' => '123',
                'modified' => '456',
                'status' => '1',
                'main_locale' => 'en',
                'locale' => 'en',
                'shared' => '1',
            ],
            [
                'id' => 84,
                'type' => '4_zones_b',
                'name' => 'My other layout',
                'description' => 'My other layout description',
                'created' => 789,
                'modified' => 111,
                'status' => Value::STATUS_PUBLISHED,
                'main_locale' => 'en',
                'locale' => 'en',
                'shared' => false,
            ],
        ];

        $expectedData = [
            [
                'id' => 42,
                'type' => '4_zones_a',
                'name' => 'My layout',
                'description' => 'My layout description',
                'shared' => true,
                'created' => 123,
                'modified' => 456,
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'status' => Value::STATUS_PUBLISHED,
            ],
            [
                'id' => 84,
                'type' => '4_zones_b',
                'name' => 'My other layout',
                'description' => 'My other layout description',
                'shared' => false,
                'created' => 789,
                'modified' => 111,
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'status' => Value::STATUS_PUBLISHED,
            ],
        ];

        $layouts = $this->mapper->mapLayouts($data);

        self::assertContainsOnlyInstancesOf(Layout::class, $layouts);
        self::assertSame($expectedData, $this->exportObjectList($layouts));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Mapper\LayoutMapper::mapZones
     */
    public function testMapZones(): void
    {
        $data = [
            [
                'identifier' => 'left',
                'layout_id' => '1',
                'status' => '1',
                'root_block_id' => '3',
                'linked_layout_id' => '3',
                'linked_zone_identifier' => 'top',
            ],
            [
                'identifier' => 'right',
                'layout_id' => 1,
                'status' => Value::STATUS_PUBLISHED,
                'root_block_id' => 4,
                'linked_layout_id' => null,
                'linked_zone_identifier' => null,
            ],
        ];

        $expectedData = [
            'left' => [
                'identifier' => 'left',
                'layoutId' => 1,
                'rootBlockId' => 3,
                'linkedLayoutId' => 3,
                'linkedZoneIdentifier' => 'top',
                'status' => Value::STATUS_PUBLISHED,
            ],
            'right' => [
                'identifier' => 'right',
                'layoutId' => 1,
                'rootBlockId' => 4,
                'linkedLayoutId' => null,
                'linkedZoneIdentifier' => null,
                'status' => Value::STATUS_PUBLISHED,
            ],
        ];

        $zones = $this->mapper->mapZones($data);

        self::assertContainsOnlyInstancesOf(Zone::class, $zones);
        self::assertSame($expectedData, $this->exportObjectList($zones));
    }
}
