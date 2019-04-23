<?php

declare(strict_types=1);

namespace Netgen\Layouts\Serializer\Normalizer\V1;

use DateTime;
use Generator;
use Netgen\Layouts\API\Service\BlockService;
use Netgen\Layouts\API\Service\LayoutService;
use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\API\Values\Layout\Zone;
use Netgen\Layouts\Exception\NotFoundException;
use Netgen\Layouts\Layout\Type\LayoutTypeInterface;
use Netgen\Layouts\Serializer\Values\VersionedValue;
use Netgen\Layouts\Serializer\Version;
use Netgen\Layouts\Utils\BackwardsCompatibility\Locales;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class LayoutNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var \Netgen\Layouts\API\Service\LayoutService
     */
    private $layoutService;

    /**
     * @var \Netgen\Layouts\API\Service\BlockService
     */
    private $blockService;

    public function __construct(LayoutService $layoutService, BlockService $blockService)
    {
        $this->layoutService = $layoutService;
        $this->blockService = $blockService;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        /** @var \Netgen\Layouts\API\Values\Layout\Layout $layout */
        $layout = $object->getValue();
        $layoutType = $layout->getLayoutType();

        $availableLocales = [];
        foreach ($layout->getAvailableLocales() as $locale) {
            $availableLocales[$locale] = Locales::getName($locale);
        }

        $data = [
            'id' => $layout->getId()->toString(),
            'type' => $layoutType->getIdentifier(),
            'published' => $layout->isPublished(),
            'has_published_state' => $this->layoutService->hasStatus($layout->getId(), Layout::STATUS_PUBLISHED),
            'created_at' => $layout->getCreated()->format(DateTime::ISO8601),
            'updated_at' => $layout->getModified()->format(DateTime::ISO8601),
            'has_archived_state' => false,
            'archive_created_at' => null,
            'archive_updated_at' => null,
            'shared' => $layout->isShared(),
            'name' => $layout->getName(),
            'description' => $layout->getDescription(),
            'main_locale' => $layout->getMainLocale(),
            'available_locales' => $availableLocales,
            'zones' => $this->normalizer->normalize($this->getZones($layout, $layoutType), $format, $context),
        ];

        try {
            $archivedLayout = $this->layoutService->loadLayoutArchive($layout->getId());

            $data['has_archived_state'] = true;
            $data['archive_created_at'] = $archivedLayout->getCreated()->format(DateTime::ISO8601);
            $data['archive_updated_at'] = $archivedLayout->getModified()->format(DateTime::ISO8601);
        } catch (NotFoundException $e) {
            // Do nothing
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        if (!$data instanceof VersionedValue) {
            return false;
        }

        return $data->getValue() instanceof Layout && $data->getVersion() === Version::API_V1;
    }

    /**
     * Returns the array with layout zones.
     */
    private function getZones(Layout $layout, LayoutTypeInterface $layoutType): Generator
    {
        foreach ($layout as $zoneIdentifier => $zone) {
            $linkedZone = $zone->getLinkedZone();

            yield [
                'identifier' => $zoneIdentifier,
                'name' => $this->getZoneName($zone, $layoutType),
                'block_ids' => $this->blockService->loadZoneBlocks($zone)->getBlockIds(),
                'allowed_block_definitions' => $this->getAllowedBlocks(
                    $zone,
                    $layoutType
                ),
                'linked_layout_id' => $linkedZone ? $linkedZone->getLayoutId()->toString() : null,
                'linked_zone_identifier' => $linkedZone ? $linkedZone->getIdentifier() : null,
            ];
        }
    }

    /**
     * Returns provided zone name.
     */
    private function getZoneName(Zone $zone, LayoutTypeInterface $layoutType): string
    {
        if ($layoutType->hasZone($zone->getIdentifier())) {
            return $layoutType->getZone($zone->getIdentifier())->getName();
        }

        return $zone->getIdentifier();
    }

    /**
     * Returns all allowed block definitions from provided zone or
     * true if all block definitions are allowed.
     *
     * @param \Netgen\Layouts\API\Values\Layout\Zone $zone
     * @param \Netgen\Layouts\Layout\Type\LayoutTypeInterface $layoutType
     *
     * @return string[]|bool
     */
    private function getAllowedBlocks(Zone $zone, LayoutTypeInterface $layoutType)
    {
        if ($layoutType->hasZone($zone->getIdentifier())) {
            $layoutTypeZone = $layoutType->getZone($zone->getIdentifier());
            $allowedBlockDefinitions = $layoutTypeZone->getAllowedBlockDefinitions();

            if (count($allowedBlockDefinitions) > 0) {
                return $allowedBlockDefinitions;
            }
        }

        return true;
    }
}
