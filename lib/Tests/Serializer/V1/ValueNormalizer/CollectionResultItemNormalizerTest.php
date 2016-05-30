<?php

namespace Netgen\BlockManager\Tests\Serializer\V1\ValueNormalizer;

use Netgen\BlockManager\Collection\ResultItem;
use Netgen\BlockManager\Value\Value;
use Netgen\BlockManager\Core\Values\Collection\Item;
use Netgen\BlockManager\Serializer\V1\ValueNormalizer\CollectionResultItemNormalizer;
use Netgen\BlockManager\Serializer\Values\VersionedValue;
use Netgen\BlockManager\Tests\Core\Stubs\Value as APIValue;

class CollectionResultItemNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Netgen\BlockManager\Serializer\V1\ValueNormalizer\CollectionResultItemNormalizer
     */
    protected $normalizer;

    public function setUp()
    {
        $this->normalizer = new CollectionResultItemNormalizer();
    }

    /**
     * @covers \Netgen\BlockManager\Serializer\V1\ValueNormalizer\CollectionResultItemNormalizer::normalize
     */
    public function testNormalize()
    {
        $item = new Item(
            array(
                'id' => 42,
                'collectionId' => 24,
            )
        );

        $value = new Value(
            array(
                'name' => 'Value name',
                'isVisible' => true,
            )
        );

        $resultItem = new ResultItem(
            array(
                'value' => $value,
                'collectionItem' => $item,
                'type' => ResultItem::TYPE_MANUAL,
                'position' => 3,
            )
        );

        self::assertEquals(
            array(
                'id' => $item->getId(),
                'collection_id' => $item->getCollectionId(),
                'position' => $resultItem->getPosition(),
                'type' => $resultItem->getType(),
                'value_id' => $value->getValueId(),
                'value_type' => $value->getValueType(),
                'name' => $value->getName(),
                'visible' => $value->isVisible(),
            ),
            $this->normalizer->normalize(new VersionedValue($resultItem, 1))
        );
    }

    /**
     * @covers \Netgen\BlockManager\Serializer\V1\ValueNormalizer\CollectionResultItemNormalizer::normalize
     */
    public function testNormalizeWithoutCollectionItem()
    {
        $value = new Value(
            array(
                'name' => 'Value name',
                'isVisible' => true,
            )
        );

        $resultItem = new ResultItem(
            array(
                'value' => $value,
                'collectionItem' => null,
                'type' => ResultItem::TYPE_DYNAMIC,
                'position' => 3,
            )
        );

        self::assertEquals(
            array(
                'id' => null,
                'collection_id' => null,
                'position' => $resultItem->getPosition(),
                'type' => $resultItem->getType(),
                'value_id' => $value->getValueId(),
                'value_type' => $value->getValueType(),
                'name' => $value->getName(),
                'visible' => $value->isVisible(),
            ),
            $this->normalizer->normalize(new VersionedValue($resultItem, 1))
        );
    }

    /**
     * @param mixed $data
     * @param bool $expected
     *
     * @covers \Netgen\BlockManager\Serializer\V1\ValueNormalizer\CollectionResultItemNormalizer::supportsNormalization
     * @dataProvider supportsNormalizationProvider
     */
    public function testSupportsNormalization($data, $expected)
    {
        self::assertEquals($expected, $this->normalizer->supportsNormalization($data));
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
            array(new APIValue(), false),
            array(new ResultItem(), false),
            array(new VersionedValue(new APIValue(), 1), false),
            array(new VersionedValue(new ResultItem(), 2), false),
            array(new VersionedValue(new ResultItem(), 1), true),
        );
    }
}
