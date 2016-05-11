<?php

namespace Netgen\BlockManager\Tests\Serializer\ValueNormalizer;

use Netgen\BlockManager\Core\Values\Page\Block;
use Netgen\BlockManager\Serializer\ValueNormalizer\BlockNormalizer;
use Netgen\BlockManager\Serializer\Values\VersionedValue;
use Netgen\BlockManager\Tests\API\Stubs\Value;

class BlockNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Netgen\BlockManager\Serializer\ValueNormalizer\BlockNormalizer
     */
    protected $blockNormalizer;

    public function setUp()
    {
        $this->blockNormalizer = new BlockNormalizer();
    }

    /**
     * @covers \Netgen\BlockManager\Serializer\ValueNormalizer\BlockNormalizer::normalize
     */
    public function testNormalize()
    {
        $block = new Block(
            array(
                'id' => 42,
                'layoutId' => 24,
                'zoneIdentifier' => 'bottom',
                'position' => 2,
                'definitionIdentifier' => 'paragraph',
                'parameters' => array(
                    'some_param' => 'some_value',
                    'some_other_param' => 'some_other_value',
                ),
                'viewType' => 'default',
                'name' => 'My block',
            )
        );

        self::assertEquals(
            array(
                'id' => $block->getId(),
                'definition_identifier' => $block->getDefinitionIdentifier(),
                'name' => $block->getName(),
                'zone_identifier' => $block->getZoneIdentifier(),
                'position' => 2,
                'layout_id' => $block->getLayoutId(),
                'parameters' => $block->getParameters(),
                'view_type' => $block->getViewType(),
            ),
            $this->blockNormalizer->normalize(new VersionedValue($block, 1))
        );
    }

    /**
     * @param mixed $data
     * @param bool $expected
     *
     * @covers \Netgen\BlockManager\Serializer\ValueNormalizer\BlockNormalizer::supportsNormalization
     * @dataProvider supportsNormalizationProvider
     */
    public function testSupportsNormalization($data, $expected)
    {
        self::assertEquals($expected, $this->blockNormalizer->supportsNormalization($data));
    }

    /**
     * Provider for {@link self::testSupportsNormalization}.
     *
     * @return array
     */
    public function supportsNormalizationProvider()
    {
        return array(
            array(null, false),
            array(true, false),
            array(false, false),
            array('block', false),
            array(array(), false),
            array(42, false),
            array(42.12, false),
            array(new Value(), false),
            array(new Block(), false),
            array(new VersionedValue(new Value(), 1), false),
            array(new VersionedValue(new Block(), 2), false),
            array(new VersionedValue(new Block(), 1), true),
        );
    }
}
