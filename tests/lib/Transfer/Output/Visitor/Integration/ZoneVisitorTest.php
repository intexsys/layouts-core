<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Transfer\Output\Visitor\Integration;

use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\API\Values\Layout\Zone;
use Netgen\Layouts\Transfer\Output\Visitor\ZoneVisitor;
use Netgen\Layouts\Transfer\Output\VisitorInterface;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Netgen\Layouts\Tests\Transfer\Output\Visitor\Integration\VisitorTest<\Netgen\Layouts\API\Values\Layout\Zone>
 */
abstract class ZoneVisitorTest extends VisitorTest
{
    public function getVisitor(): VisitorInterface
    {
        return new ZoneVisitor($this->blockService);
    }

    public function acceptDataProvider(): array
    {
        return [
            [new Zone(), true],
            [new Layout(), false],
            [new Block(), false],
        ];
    }

    public function visitDataProvider(): array
    {
        return [
            [function (): Zone { return $this->layoutService->loadLayout(Uuid::fromString('71cbe281-430c-51d5-8e21-c3cc4e656dac'))->getZone('top'); }, 'zone/zone_2_top.json'],
            [function (): Zone { return $this->layoutService->loadLayout(Uuid::fromString('71cbe281-430c-51d5-8e21-c3cc4e656dac'))->getZone('right'); }, 'zone/zone_2_right.json'],
            [function (): Zone { return $this->layoutService->loadLayout(Uuid::fromString('7900306c-0351-5f0a-9b33-5d4f5a1f3943'))->getZone('bottom'); }, 'zone/zone_6_bottom.json'],
        ];
    }
}
