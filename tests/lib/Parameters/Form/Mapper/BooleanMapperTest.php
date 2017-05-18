<?php

namespace Netgen\BlockManager\Tests\Parameters\Form\Mapper;

use Netgen\BlockManager\Parameters\Form\Mapper\BooleanMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class BooleanMapperTest extends TestCase
{
    /**
     * @var \Netgen\BlockManager\Parameters\Form\Mapper\BooleanMapper
     */
    protected $mapper;

    public function setUp()
    {
        $this->mapper = new BooleanMapper();
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\Form\Mapper\BooleanMapper::getFormType
     */
    public function testGetFormType()
    {
        $this->assertEquals(CheckboxType::class, $this->mapper->getFormType());
    }
}
