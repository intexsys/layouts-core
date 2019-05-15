<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\View\Matcher\Zone;

use Netgen\Layouts\API\Values\Block\BlockList;
use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\API\Values\Layout\Zone;
use Netgen\Layouts\API\Values\Layout\ZoneList;
use Netgen\Layouts\Tests\API\Stubs\Value;
use Netgen\Layouts\Tests\View\Stubs\View;
use Netgen\Layouts\View\Matcher\Zone\Identifier;
use Netgen\Layouts\View\View\ZoneView;
use Netgen\Layouts\View\View\ZoneView\ZoneReference;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase
{
    /**
     * @var \Netgen\Layouts\View\Matcher\MatcherInterface
     */
    private $matcher;

    protected function setUp(): void
    {
        $this->matcher = new Identifier();
    }

    /**
     * @covers \Netgen\Layouts\View\Matcher\Zone\Identifier::match
     * @dataProvider matchProvider
     */
    public function testMatch(array $config, bool $expected): void
    {
        $layout = Layout::fromArray(['zones' => new ZoneList(['left' => Zone::fromArray(['identifier' => 'left'])])]);

        $view = new ZoneView(new ZoneReference($layout, 'left'), new BlockList());

        self::assertSame($expected, $this->matcher->match($view, $config));
    }

    public function matchProvider(): array
    {
        return [
            [[], false],
            [['other'], false],
            [['left'], true],
            [['other1', 'other2'], false],
            [['other', 'left'], true],
        ];
    }

    /**
     * @covers \Netgen\Layouts\View\Matcher\Zone\Identifier::match
     */
    public function testMatchWithNoZoneView(): void
    {
        self::assertFalse($this->matcher->match(new View(new Value()), []));
    }
}
