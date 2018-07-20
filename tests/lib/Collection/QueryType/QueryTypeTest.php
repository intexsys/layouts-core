<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Tests\Collection\QueryType;

use Netgen\BlockManager\Collection\QueryType\QueryType;
use Netgen\BlockManager\Core\Values\Collection\Query;
use Netgen\BlockManager\Tests\Collection\Stubs\QueryTypeHandler;
use PHPUnit\Framework\TestCase;

final class QueryTypeTest extends TestCase
{
    /**
     * @var \Netgen\BlockManager\Collection\QueryType\QueryType
     */
    private $queryType;

    public function setUp(): void
    {
        $this->queryType = QueryType::fromArray(
            [
                'handler' => new QueryTypeHandler(['val1', 'val2']),
                'type' => 'query_type',
                'isEnabled' => false,
                'name' => 'Query type',
            ]
        );
    }

    /**
     * @covers \Netgen\BlockManager\Collection\QueryType\QueryType::getType
     */
    public function testGetType(): void
    {
        $this->assertSame('query_type', $this->queryType->getType());
    }

    /**
     * @covers \Netgen\BlockManager\Collection\QueryType\QueryType::isEnabled
     */
    public function testIsEnabled(): void
    {
        $this->assertFalse($this->queryType->isEnabled());
    }

    /**
     * @covers \Netgen\BlockManager\Collection\QueryType\QueryType::getName
     */
    public function testGetName(): void
    {
        $this->assertSame('Query type', $this->queryType->getName());
    }

    /**
     * @covers \Netgen\BlockManager\Collection\QueryType\QueryType::getValues
     */
    public function testGetValues(): void
    {
        $this->assertSame(['val1', 'val2'], $this->queryType->getValues(new Query()));
    }

    /**
     * @covers \Netgen\BlockManager\Collection\QueryType\QueryType::getCount
     */
    public function testGetCount(): void
    {
        $this->assertSame(2, $this->queryType->getCount(new Query()));
    }

    /**
     * @covers \Netgen\BlockManager\Collection\QueryType\QueryType::isContextual
     */
    public function testIsContextual(): void
    {
        $this->assertFalse($this->queryType->isContextual(new Query()));
    }
}
