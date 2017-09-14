<?php

namespace Netgen\BlockManager\Serializer\Normalizer\V1;

use Netgen\BlockManager\API\Values\Collection\Collection;
use Netgen\BlockManager\Serializer\SerializerAwareTrait;
use Netgen\BlockManager\Serializer\Values\VersionedValue;
use Netgen\BlockManager\Serializer\Version;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;

class CollectionNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var \Netgen\BlockManager\API\Values\Collection\Collection $collection */
        $collection = $object->getValue();

        return array(
            'id' => $collection->getId(),
            'type' => $collection->getType(),
            'is_translatable' => $collection->isTranslatable(),
            'main_locale' => $collection->getMainLocale(),
            'always_available' => $collection->isAlwaysAvailable(),
            'available_locales' => $collection->getAvailableLocales(),
        );
    }

    public function supportsNormalization($data, $format = null)
    {
        if (!$data instanceof VersionedValue) {
            return false;
        }

        return $data->getValue() instanceof Collection && $data->getVersion() === Version::API_V1;
    }
}
