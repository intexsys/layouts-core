<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Core\Service\Mapper;

use Netgen\BlockManager\API\Values\Block\Block as APIBlock;
use Netgen\BlockManager\API\Values\Collection\Collection;
use Netgen\BlockManager\Block\BlockDefinitionInterface;
use Netgen\BlockManager\Block\ContainerDefinitionInterface;
use Netgen\BlockManager\Block\NullBlockDefinition;
use Netgen\BlockManager\Block\Registry\BlockDefinitionRegistryInterface;
use Netgen\BlockManager\Core\Values\Block\Block;
use Netgen\BlockManager\Core\Values\Block\Placeholder;
use Netgen\BlockManager\Core\Values\LazyCollection;
use Netgen\BlockManager\Exception\Block\BlockDefinitionException;
use Netgen\BlockManager\Exception\NotFoundException;
use Netgen\BlockManager\Persistence\Handler\BlockHandlerInterface;
use Netgen\BlockManager\Persistence\Handler\CollectionHandlerInterface;
use Netgen\BlockManager\Persistence\Values\Block\Block as PersistenceBlock;
use Netgen\BlockManager\Persistence\Values\Collection\Collection as PersistenceCollection;

final class BlockMapper
{
    /**
     * @var \Netgen\BlockManager\Persistence\Handler\BlockHandlerInterface
     */
    private $blockHandler;

    /**
     * @var \Netgen\BlockManager\Persistence\Handler\CollectionHandlerInterface
     */
    private $collectionHandler;

    /**
     * @var \Netgen\BlockManager\Core\Service\Mapper\CollectionMapper
     */
    private $collectionMapper;

    /**
     * @var \Netgen\BlockManager\Core\Service\Mapper\ParameterMapper
     */
    private $parameterMapper;

    /**
     * @var \Netgen\BlockManager\Core\Service\Mapper\ConfigMapper
     */
    private $configMapper;

    /**
     * @var \Netgen\BlockManager\Block\Registry\BlockDefinitionRegistryInterface
     */
    private $blockDefinitionRegistry;

    public function __construct(
        BlockHandlerInterface $blockHandler,
        CollectionHandlerInterface $collectionHandler,
        CollectionMapper $collectionMapper,
        ParameterMapper $parameterMapper,
        ConfigMapper $configMapper,
        BlockDefinitionRegistryInterface $blockDefinitionRegistry
    ) {
        $this->blockHandler = $blockHandler;
        $this->collectionHandler = $collectionHandler;
        $this->collectionMapper = $collectionMapper;
        $this->parameterMapper = $parameterMapper;
        $this->configMapper = $configMapper;
        $this->blockDefinitionRegistry = $blockDefinitionRegistry;
    }

    /**
     * Builds the API block value from persistence one.
     *
     * If not empty, the first available locale in $locales array will be returned.
     *
     * If the block is always available and $useMainLocale is set to true,
     * block in main locale will be returned if none of the locales in $locales
     * array are found.
     *
     * @throws \Netgen\BlockManager\Exception\NotFoundException If the block does not have any requested translations
     */
    public function mapBlock(PersistenceBlock $block, ?array $locales = null, bool $useMainLocale = true): APIBlock
    {
        try {
            $blockDefinition = $this->blockDefinitionRegistry->getBlockDefinition(
                $block->definitionIdentifier
            );
        } catch (BlockDefinitionException $e) {
            $blockDefinition = new NullBlockDefinition($block->definitionIdentifier);
        }

        $locales = !empty($locales) ? $locales : [$block->mainLocale];
        if ($useMainLocale && $block->alwaysAvailable) {
            $locales[] = $block->mainLocale;
        }

        $validLocales = array_unique(array_intersect($locales, $block->availableLocales));
        if (empty($validLocales)) {
            throw new NotFoundException('block', $block->id);
        }

        $blockLocale = array_values($validLocales)[0];
        $untranslatableParams = $this->parameterMapper->extractUntranslatableParameters(
            $blockDefinition,
            $block->parameters[$block->mainLocale]
        );

        $blockData = [
            'id' => $block->id,
            'layoutId' => $block->layoutId,
            'definition' => $blockDefinition,
            'viewType' => $block->viewType,
            'itemViewType' => $block->itemViewType,
            'name' => $block->name,
            'parentPosition' => $block->position,
            'status' => $block->status,
            'placeholders' => $this->mapPlaceholders($block, $blockDefinition, $locales),
            'collections' => new LazyCollection(
                function () use ($block, $locales): array {
                    return array_map(
                        function (PersistenceCollection $collection) use ($locales): Collection {
                            return $this->collectionMapper->mapCollection($collection, $locales);
                        },
                        $this->loadCollections($block)
                    );
                }
            ),
            'configs' => $this->configMapper->mapConfig($block->config, $blockDefinition->getConfigDefinitions()),
            'isTranslatable' => $block->isTranslatable,
            'mainLocale' => $block->mainLocale,
            'alwaysAvailable' => $block->alwaysAvailable,
            'availableLocales' => $block->availableLocales,
            'locale' => $blockLocale,
            'parameters' => $this->parameterMapper->mapParameters(
                $blockDefinition,
                $untranslatableParams + $block->parameters[$blockLocale]
            ),
        ];

        return new Block($blockData);
    }

    /**
     * Loads all persistence collections belonging to the provided block.
     *
     * @return \Netgen\BlockManager\Persistence\Values\Collection\Collection[]
     */
    private function loadCollections(PersistenceBlock $block): array
    {
        $collectionReferences = $this->blockHandler->loadCollectionReferences($block);

        $collections = [];

        foreach ($collectionReferences as $collectionReference) {
            $collections[$collectionReference->identifier] = $this->collectionHandler->loadCollection(
                $collectionReference->collectionId,
                $collectionReference->collectionStatus
            );
        }

        return $collections;
    }

    /**
     * Maps the placeholder from persistence parameters.
     *
     * @return \Netgen\BlockManager\Core\Values\Block\Placeholder[]
     */
    private function mapPlaceholders(PersistenceBlock $block, BlockDefinitionInterface $blockDefinition, ?array $locales = null): array
    {
        if (!$blockDefinition instanceof ContainerDefinitionInterface) {
            return [];
        }

        $placeholders = [];
        foreach ($blockDefinition->getPlaceholders() as $placeholderIdentifier) {
            $placeholders[$placeholderIdentifier] = new Placeholder(
                [
                    'identifier' => $placeholderIdentifier,
                    'blocks' => new LazyCollection(
                        function () use ($block, $placeholderIdentifier, $locales): array {
                            return array_map(
                                function (PersistenceBlock $childBlock) use ($locales): APIBlock {
                                    return $this->mapBlock($childBlock, $locales, false);
                                },
                                $this->blockHandler->loadChildBlocks($block, $placeholderIdentifier)
                            );
                        }
                    ),
                ]
            );
        }

        return $placeholders;
    }
}
