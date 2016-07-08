<?php

namespace Netgen\BlockManager\Serializer\V1\ValueNormalizer;

use Netgen\BlockManager\API\Service\LayoutService;
use Netgen\BlockManager\API\Values\Page\Block;
use Netgen\BlockManager\API\Values\Page\Layout;
use Netgen\BlockManager\API\Values\Page\LayoutReference;
use Netgen\BlockManager\Configuration\Registry\LayoutTypeRegistryInterface;
use Netgen\BlockManager\Serializer\Values\VersionedValue;
use Netgen\BlockManager\Serializer\Version;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use DateTime;

class LayoutNormalizer implements NormalizerInterface
{
    /**
     * @var \Netgen\BlockManager\Configuration\Registry\LayoutTypeRegistryInterface
     */
    protected $layoutTypeRegistry;

    /**
     * @var \Netgen\BlockManager\API\Service\LayoutService
     */
    protected $layoutService;

    /**
     * Constructor.
     *
     * @param \Netgen\BlockManager\Configuration\Registry\LayoutTypeRegistryInterface $layoutTypeRegistry
     * @param \Netgen\BlockManager\API\Service\LayoutService $layoutService
     */
    public function __construct(LayoutTypeRegistryInterface $layoutTypeRegistry, LayoutService $layoutService)
    {
        $this->layoutTypeRegistry = $layoutTypeRegistry;
        $this->layoutService = $layoutService;
    }

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param \Netgen\BlockManager\Serializer\Values\VersionedValue $object
     * @param string $format
     * @param array $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        /** @var \Netgen\BlockManager\API\Values\Page\Layout $layout */
        $layout = $object->getValue();

        $data = array(
            'id' => $layout->getId(),
            'type' => $layout->getType(),
            'published' => $layout->getStatus() === Layout::STATUS_PUBLISHED ?
                true :
                false,
            'has_published_state' => $this->layoutService->isPublished($layout),
            'created_at' => $layout->getCreated()->format(DateTime::ISO8601),
            'updated_at' => $layout->getModified()->format(DateTime::ISO8601),
            'shared' => $layout->isShared(),
            'name' => $layout->getName(),
        );

        if ($layout instanceof Layout) {
            $data['zones'] = $this->getZones($layout);
        }

        return $data;
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed $data
     * @param string $format
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        if (!$data instanceof VersionedValue) {
            return false;
        }

        return $data->getValue() instanceof LayoutReference && $data->getVersion() === Version::API_V1;
    }

    /**
     * Returns the array with layout zones.
     *
     * @param \Netgen\BlockManager\API\Values\Page\Layout $layout
     *
     * @return array
     */
    protected function getZones(Layout $layout)
    {
        $zones = array();
        $layoutType = $this->layoutTypeRegistry->getLayoutType($layout->getType());

        foreach ($layout->getZones() as $zoneIdentifier => $zone) {
            $allowedBlockDefinitions = true;

            if ($layoutType->hasZone($zoneIdentifier)) {
                $layoutTypeZone = $layoutType->getZone($zoneIdentifier);
                if (!empty($layoutTypeZone->getAllowedBlockDefinitions())) {
                    $allowedBlockDefinitions = $layoutTypeZone->getAllowedBlockDefinitions();
                }
            }

            $zones[] = array(
                'identifier' => $zoneIdentifier,
                'block_ids' => array_map(
                    function (Block $block) {
                        return $block->getId();
                    },
                    $zone->getBlocks()
                ),
                'allowed_block_definitions' => $allowedBlockDefinitions,
                'linked_layout_id' => $zone->getLinkedLayoutId(),
                'linked_zone_identifier' => $zone->getLinkedZoneIdentifier(),
            );
        }

        return $zones;
    }
}
