<?php

namespace Netgen\BlockManager\Serializer\Normalizer\V1;

use Netgen\BlockManager\API\Values\Collection\Item;
use Netgen\BlockManager\Exception\Item\ItemException;
use Netgen\BlockManager\Item\ItemLoaderInterface;
use Netgen\BlockManager\Serializer\Values\VersionedValue;
use Netgen\BlockManager\Serializer\Version;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CollectionItemNormalizer implements NormalizerInterface
{
    /**
     * @var \Netgen\BlockManager\Item\ItemLoaderInterface
     */
    protected $itemLoader;

    public function __construct(ItemLoaderInterface $itemLoader)
    {
        $this->itemLoader = $itemLoader;
    }

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var \Netgen\BlockManager\API\Values\Collection\Item $item */
        $item = $object->getValue();

        $data = array(
            'id' => $item->getId(),
            'collection_id' => $item->getCollectionId(),
            'position' => $item->getPosition(),
            'type' => $item->getType(),
            'value_id' => $item->getValueId(),
            'value_type' => $item->getValueType(),
            'name' => null,
            'visible' => null,
        );

        try {
            $value = $this->itemLoader->load($item->getValueId(), $item->getValueType());
            $data['name'] = $value->getName();
            $data['visible'] = $value->isVisible();
        } catch (ItemException $e) {
            // Do nothing
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        if (!$data instanceof VersionedValue) {
            return false;
        }

        return $data->getValue() instanceof Item && $data->getVersion() === Version::API_V1;
    }
}
