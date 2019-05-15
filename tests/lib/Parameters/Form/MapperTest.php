<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Parameters\Form;

use Netgen\Layouts\Parameters\Form\Mapper;
use Netgen\Layouts\Parameters\ParameterDefinition;
use PHPUnit\Framework\TestCase;

final class MapperTest extends TestCase
{
    /**
     * @var \Netgen\Layouts\Parameters\Form\MapperInterface
     */
    private $mapper;

    protected function setUp(): void
    {
        $this->mapper = $this->getMockForAbstractClass(Mapper::class);
    }

    /**
     * @covers \Netgen\Layouts\Parameters\Form\Mapper::mapOptions
     */
    public function testMapOptions(): void
    {
        self::assertSame([], $this->mapper->mapOptions(new ParameterDefinition()));
    }
}
