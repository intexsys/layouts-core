<?php

declare(strict_types=1);

namespace Netgen\Layouts\Core\Service;

use Generator;
use Netgen\Layouts\API\Service\BlockService as BlockServiceInterface;
use Netgen\Layouts\API\Service\LayoutService as APILayoutService;
use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\API\Values\Block\BlockCreateStruct as APIBlockCreateStruct;
use Netgen\Layouts\API\Values\Block\BlockList;
use Netgen\Layouts\API\Values\Block\BlockUpdateStruct as APIBlockUpdateStruct;
use Netgen\Layouts\API\Values\Collection\QueryCreateStruct as APIQueryCreateStruct;
use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\API\Values\Layout\Zone;
use Netgen\Layouts\API\Values\Value;
use Netgen\Layouts\Block\BlockDefinitionInterface;
use Netgen\Layouts\Block\ContainerDefinitionInterface;
use Netgen\Layouts\Core\Mapper\BlockMapper;
use Netgen\Layouts\Core\Mapper\ConfigMapper;
use Netgen\Layouts\Core\Mapper\ParameterMapper;
use Netgen\Layouts\Core\StructBuilder\BlockStructBuilder;
use Netgen\Layouts\Core\Validator\BlockValidator;
use Netgen\Layouts\Exception\BadStateException;
use Netgen\Layouts\Exception\NotFoundException;
use Netgen\Layouts\Persistence\Handler\BlockHandlerInterface;
use Netgen\Layouts\Persistence\Handler\CollectionHandlerInterface;
use Netgen\Layouts\Persistence\Handler\LayoutHandlerInterface;
use Netgen\Layouts\Persistence\TransactionHandlerInterface;
use Netgen\Layouts\Persistence\Values\Block\Block as PersistenceBlock;
use Netgen\Layouts\Persistence\Values\Block\BlockCreateStruct;
use Netgen\Layouts\Persistence\Values\Block\BlockTranslationUpdateStruct;
use Netgen\Layouts\Persistence\Values\Block\BlockUpdateStruct;
use Netgen\Layouts\Persistence\Values\Collection\CollectionCreateStruct;
use Netgen\Layouts\Persistence\Values\Collection\QueryCreateStruct;

final class BlockService extends Service implements BlockServiceInterface
{
    /**
     * @var \Netgen\Layouts\Core\Validator\BlockValidator
     */
    private $validator;

    /**
     * @var \Netgen\Layouts\Core\Mapper\BlockMapper
     */
    private $mapper;

    /**
     * @var \Netgen\Layouts\Core\StructBuilder\BlockStructBuilder
     */
    private $structBuilder;

    /**
     * @var \Netgen\Layouts\Core\Mapper\ParameterMapper
     */
    private $parameterMapper;

    /**
     * @var \Netgen\Layouts\Core\Mapper\ConfigMapper
     */
    private $configMapper;

    /**
     * @var \Netgen\Layouts\Persistence\Handler\BlockHandlerInterface
     */
    private $blockHandler;

    /**
     * @var \Netgen\Layouts\Persistence\Handler\LayoutHandlerInterface
     */
    private $layoutHandler;

    /**
     * @var \Netgen\Layouts\Persistence\Handler\CollectionHandlerInterface
     */
    private $collectionHandler;

    /**
     * @var \Netgen\Layouts\API\Service\LayoutService
     */
    private $layoutService;

    public function __construct(
        TransactionHandlerInterface $transactionHandler,
        BlockValidator $validator,
        BlockMapper $mapper,
        BlockStructBuilder $structBuilder,
        ParameterMapper $parameterMapper,
        ConfigMapper $configMapper,
        APILayoutService $layoutService,
        BlockHandlerInterface $blockHandler,
        LayoutHandlerInterface $layoutHandler,
        CollectionHandlerInterface $collectionHandler
    ) {
        parent::__construct($transactionHandler);

        $this->validator = $validator;
        $this->mapper = $mapper;
        $this->structBuilder = $structBuilder;
        $this->parameterMapper = $parameterMapper;
        $this->configMapper = $configMapper;
        $this->layoutService = $layoutService;
        $this->blockHandler = $blockHandler;
        $this->layoutHandler = $layoutHandler;
        $this->collectionHandler = $collectionHandler;
    }

    public function loadBlock($blockId, ?array $locales = null, bool $useMainLocale = true): Block
    {
        $this->validator->validateId($blockId, 'blockId');

        $block = $this->blockHandler->loadBlock($blockId, Value::STATUS_PUBLISHED);

        if ($block->parentId === null) {
            // We do not allow loading root zone blocks
            throw new NotFoundException('block', $blockId);
        }

        return $this->mapper->mapBlock($block, $locales, $useMainLocale);
    }

    public function loadBlockDraft($blockId, ?array $locales = null, bool $useMainLocale = true): Block
    {
        $this->validator->validateId($blockId, 'blockId');

        $block = $this->blockHandler->loadBlock($blockId, Value::STATUS_DRAFT);

        if ($block->parentId === null) {
            // We do not allow loading root zone blocks
            throw new NotFoundException('block', $blockId);
        }

        return $this->mapper->mapBlock($block, $locales, $useMainLocale);
    }

    public function loadZoneBlocks(Zone $zone, ?array $locales = null, bool $useMainLocale = true): BlockList
    {
        $persistenceZone = $this->layoutHandler->loadZone(
            $zone->getLayoutId(),
            $zone->getStatus(),
            $zone->getIdentifier()
        );

        $rootBlock = $this->blockHandler->loadBlock(
            $persistenceZone->rootBlockId,
            $persistenceZone->status
        );

        return new BlockList(
            array_values(
                iterator_to_array(
                    $this->filterUntranslatedBlocks(
                        $this->blockHandler->loadChildBlocks($rootBlock),
                        $locales,
                        $useMainLocale
                    )
                )
            )
        );
    }

    public function loadLayoutBlocks(Layout $layout, ?array $locales = null, bool $useMainLocale = true): BlockList
    {
        $persistenceLayout = $this->layoutHandler->loadLayout(
            $layout->getId(),
            $layout->getStatus()
        );

        // We filter out all root blocks, since we do not allow loading those
        $persistenceBlocks = array_filter(
            $this->blockHandler->loadLayoutBlocks($persistenceLayout),
            static function (PersistenceBlock $persistenceBlock): bool {
                return $persistenceBlock->parentId !== null;
            }
        );

        return new BlockList(
            array_values(
                iterator_to_array(
                    $this->filterUntranslatedBlocks(
                        $persistenceBlocks,
                        $locales,
                        $useMainLocale
                    )
                )
            )
        );
    }

    public function hasPublishedState(Block $block): bool
    {
        return $this->blockHandler->blockExists($block->getId(), Value::STATUS_PUBLISHED);
    }

    public function createBlock(APIBlockCreateStruct $blockCreateStruct, Block $targetBlock, string $placeholder, ?int $position = null): Block
    {
        if (!$targetBlock->isDraft()) {
            throw new BadStateException('targetBlock', 'Blocks can only be created in blocks in draft status.');
        }

        $this->validator->validateIdentifier($placeholder, 'placeholder');
        $this->validator->validatePosition($position, 'position');
        $this->validator->validateBlockCreateStruct($blockCreateStruct);

        $targetBlockDefinition = $targetBlock->getDefinition();

        if (!$targetBlockDefinition instanceof ContainerDefinitionInterface) {
            throw new BadStateException('targetBlock', 'Target block is not a container.');
        }

        if (!in_array($placeholder, $targetBlockDefinition->getPlaceholders(), true)) {
            throw new BadStateException('placeholder', 'Target block does not have the specified placeholder.');
        }

        if ($blockCreateStruct->getDefinition() instanceof ContainerDefinitionInterface) {
            throw new BadStateException('blockCreateStruct', 'Containers cannot be placed inside containers.');
        }

        if ($blockCreateStruct->isTranslatable && !$targetBlock->isTranslatable()) {
            $blockCreateStruct->isTranslatable = false;
        }

        $persistenceBlock = $this->blockHandler->loadBlock($targetBlock->getId(), Value::STATUS_DRAFT);

        return $this->internalCreateBlock($blockCreateStruct, $persistenceBlock, $placeholder, $position);
    }

    public function createBlockInZone(APIBlockCreateStruct $blockCreateStruct, Zone $zone, ?int $position = null): Block
    {
        if (!$zone->isDraft()) {
            throw new BadStateException('zone', 'Blocks can only be created in zones in draft status.');
        }

        $layout = $this->layoutService->loadLayoutDraft($zone->getLayoutId());

        $persistenceZone = $this->layoutHandler->loadZone($zone->getLayoutId(), Value::STATUS_DRAFT, $zone->getIdentifier());

        $this->validator->validatePosition($position, 'position');
        $this->validator->validateBlockCreateStruct($blockCreateStruct);

        if (
            !$layout->getLayoutType()->isBlockAllowedInZone(
                $blockCreateStruct->getDefinition(),
                $persistenceZone->identifier
            )
        ) {
            throw new BadStateException('zone', 'Block is not allowed in specified zone.');
        }

        $rootBlock = $this->blockHandler->loadBlock(
            $persistenceZone->rootBlockId,
            $persistenceZone->status
        );

        return $this->internalCreateBlock($blockCreateStruct, $rootBlock, 'root', $position);
    }

    public function updateBlock(Block $block, APIBlockUpdateStruct $blockUpdateStruct): Block
    {
        if (!$block->isDraft()) {
            throw new BadStateException('block', 'Only draft blocks can be updated.');
        }

        $persistenceBlock = $this->blockHandler->loadBlock($block->getId(), Value::STATUS_DRAFT);

        $this->validator->validateBlockUpdateStruct($block, $blockUpdateStruct);

        if (!in_array($blockUpdateStruct->locale, $persistenceBlock->availableLocales, true)) {
            throw new BadStateException('block', 'Block does not have the specified translation.');
        }

        $blockDefinition = $block->getDefinition();

        $updatedBlock = $this->transaction(
            function () use ($blockDefinition, $persistenceBlock, $blockUpdateStruct): PersistenceBlock {
                $persistenceBlock = $this->updateBlockTranslations(
                    $blockDefinition,
                    $persistenceBlock,
                    $blockUpdateStruct
                );

                return $this->blockHandler->updateBlock(
                    $persistenceBlock,
                    BlockUpdateStruct::fromArray(
                        [
                            'viewType' => $blockUpdateStruct->viewType,
                            'itemViewType' => $blockUpdateStruct->itemViewType,
                            'name' => $blockUpdateStruct->name,
                            'alwaysAvailable' => $blockUpdateStruct->alwaysAvailable,
                            'config' => iterator_to_array(
                                $this->configMapper->serializeValues(
                                    $blockUpdateStruct->getConfigStructs(),
                                    $blockDefinition->getConfigDefinitions(),
                                    $persistenceBlock->config
                                )
                            ),
                        ]
                    )
                );
            }
        );

        return $this->mapper->mapBlock($updatedBlock, [$block->getLocale()]);
    }

    public function copyBlock(Block $block, Block $targetBlock, string $placeholder, ?int $position = null): Block
    {
        if (!$block->isDraft()) {
            throw new BadStateException('block', 'Only draft blocks can be copied.');
        }

        if (!$targetBlock->isDraft()) {
            throw new BadStateException('targetBlock', 'You can only copy blocks to draft blocks.');
        }

        $persistenceBlock = $this->blockHandler->loadBlock($block->getId(), Value::STATUS_DRAFT);
        $persistenceTargetBlock = $this->blockHandler->loadBlock($targetBlock->getId(), Value::STATUS_DRAFT);

        if ($persistenceBlock->layoutId !== $persistenceTargetBlock->layoutId) {
            throw new BadStateException('targetBlock', 'You can only copy block to blocks in the same layout.');
        }

        $this->validator->validateIdentifier($placeholder, 'placeholder');
        $this->validator->validatePosition($position, 'position');

        $targetBlockDefinition = $targetBlock->getDefinition();

        if (!$targetBlockDefinition instanceof ContainerDefinitionInterface) {
            throw new BadStateException('targetBlock', 'Target block is not a container.');
        }

        if (!in_array($placeholder, $targetBlockDefinition->getPlaceholders(), true)) {
            throw new BadStateException('placeholder', 'Target block does not have the specified placeholder.');
        }

        if ($block->getDefinition() instanceof ContainerDefinitionInterface) {
            throw new BadStateException('block', 'Containers cannot be placed inside containers.');
        }

        $copiedBlock = $this->transaction(
            function () use ($persistenceBlock, $persistenceTargetBlock, $placeholder, $position): PersistenceBlock {
                return $this->blockHandler->copyBlock($persistenceBlock, $persistenceTargetBlock, $placeholder, $position);
            }
        );

        return $this->mapper->mapBlock($copiedBlock, [$block->getLocale()]);
    }

    public function copyBlockToZone(Block $block, Zone $zone, ?int $position = null): Block
    {
        if (!$block->isDraft()) {
            throw new BadStateException('block', 'Only draft blocks can be copied.');
        }

        if (!$zone->isDraft()) {
            throw new BadStateException('zone', 'You can only copy blocks in draft zones.');
        }

        $layout = $this->layoutService->loadLayoutDraft($zone->getLayoutId());

        $persistenceBlock = $this->blockHandler->loadBlock($block->getId(), Value::STATUS_DRAFT);
        $persistenceZone = $this->layoutHandler->loadZone($zone->getLayoutId(), Value::STATUS_DRAFT, $zone->getIdentifier());

        if ($persistenceBlock->layoutId !== $persistenceZone->layoutId) {
            throw new BadStateException('zone', 'You can only copy block to zone in the same layout.');
        }

        if (!$layout->getLayoutType()->isBlockAllowedInZone($block->getDefinition(), $persistenceZone->identifier)) {
            throw new BadStateException('zone', 'Block is not allowed in specified zone.');
        }

        $this->validator->validatePosition($position, 'position');

        $rootBlock = $this->blockHandler->loadBlock($persistenceZone->rootBlockId, $persistenceZone->status);

        $copiedBlock = $this->transaction(
            function () use ($persistenceBlock, $rootBlock, $position): PersistenceBlock {
                return $this->blockHandler->copyBlock($persistenceBlock, $rootBlock, 'root', $position);
            }
        );

        return $this->mapper->mapBlock($copiedBlock, [$block->getLocale()]);
    }

    public function moveBlock(Block $block, Block $targetBlock, string $placeholder, int $position): Block
    {
        if (!$block->isDraft()) {
            throw new BadStateException('block', 'Only draft blocks can be moved.');
        }

        if (!$targetBlock->isDraft()) {
            throw new BadStateException('targetBlock', 'You can only move blocks to draft blocks.');
        }

        $persistenceBlock = $this->blockHandler->loadBlock($block->getId(), Value::STATUS_DRAFT);
        $persistenceTargetBlock = $this->blockHandler->loadBlock($targetBlock->getId(), Value::STATUS_DRAFT);

        if ($persistenceBlock->layoutId !== $persistenceTargetBlock->layoutId) {
            throw new BadStateException('targetBlock', 'You can only move block to blocks in the same layout.');
        }

        $this->validator->validateIdentifier($placeholder, 'placeholder');
        $this->validator->validatePosition($position, 'position', true);

        $targetBlockDefinition = $targetBlock->getDefinition();

        if (!$targetBlockDefinition instanceof ContainerDefinitionInterface) {
            throw new BadStateException('targetBlock', 'Target block is not a container.');
        }

        if (!in_array($placeholder, $targetBlockDefinition->getPlaceholders(), true)) {
            throw new BadStateException('placeholder', 'Target block does not have the specified placeholder.');
        }

        if ($block->getDefinition() instanceof ContainerDefinitionInterface) {
            throw new BadStateException('block', 'Containers cannot be placed inside containers.');
        }

        $movedBlock = $this->internalMoveBlock($persistenceBlock, $persistenceTargetBlock, $placeholder, $position);

        return $this->mapper->mapBlock($movedBlock, [$block->getLocale()]);
    }

    public function moveBlockToZone(Block $block, Zone $zone, int $position): Block
    {
        if (!$block->isDraft()) {
            throw new BadStateException('block', 'Only draft blocks can be moved.');
        }

        if (!$zone->isDraft()) {
            throw new BadStateException('zone', 'You can only move blocks in draft zones.');
        }

        $this->validator->validatePosition($position, 'position', true);

        $layout = $this->layoutService->loadLayoutDraft($zone->getLayoutId());

        $persistenceBlock = $this->blockHandler->loadBlock($block->getId(), Value::STATUS_DRAFT);
        $persistenceZone = $this->layoutHandler->loadZone($zone->getLayoutId(), Value::STATUS_DRAFT, $zone->getIdentifier());

        if ($persistenceBlock->layoutId !== $persistenceZone->layoutId) {
            throw new BadStateException('zone', 'You can only move block to zone in the same layout.');
        }

        if (!$layout->getLayoutType()->isBlockAllowedInZone($block->getDefinition(), $persistenceZone->identifier)) {
            throw new BadStateException('zone', 'Block is not allowed in specified zone.');
        }

        $rootBlock = $this->blockHandler->loadBlock($persistenceZone->rootBlockId, $persistenceZone->status);

        $movedBlock = $this->internalMoveBlock($persistenceBlock, $rootBlock, 'root', $position);

        return $this->mapper->mapBlock($movedBlock, [$block->getLocale()]);
    }

    public function restoreBlock(Block $block): Block
    {
        if (!$block->isDraft()) {
            throw new BadStateException('block', 'Only draft blocks can be restored.');
        }

        $draftBlock = $this->blockHandler->loadBlock($block->getId(), Value::STATUS_DRAFT);
        $draftLayout = $this->layoutHandler->loadLayout($draftBlock->layoutId, Value::STATUS_DRAFT);

        $draftBlock = $this->transaction(
            function () use ($draftBlock, $draftLayout): PersistenceBlock {
                $draftBlock = $this->blockHandler->restoreBlock($draftBlock, Value::STATUS_PUBLISHED);

                foreach ($draftLayout->availableLocales as $locale) {
                    if (!in_array($locale, $draftBlock->availableLocales, true)) {
                        $draftBlock = $this->blockHandler->createBlockTranslation(
                            $draftBlock,
                            $locale,
                            $draftBlock->mainLocale
                        );
                    }
                }

                $draftBlock = $this->blockHandler->setMainTranslation($draftBlock, $draftLayout->mainLocale);

                foreach ($draftBlock->availableLocales as $blockLocale) {
                    if (!in_array($blockLocale, $draftLayout->availableLocales, true)) {
                        $draftBlock = $this->blockHandler->deleteBlockTranslation($draftBlock, $blockLocale);
                    }
                }

                return $draftBlock;
            }
        );

        return $this->mapper->mapBlock($draftBlock);
    }

    public function enableTranslations(Block $block): Block
    {
        if (!$block->isDraft()) {
            throw new BadStateException('block', 'You can only enable translations for draft blocks.');
        }

        $persistenceBlock = $this->blockHandler->loadBlock($block->getId(), Value::STATUS_DRAFT);

        if ($persistenceBlock->isTranslatable) {
            throw new BadStateException('block', 'Block is already translatable.');
        }

        if ($persistenceBlock->parentId !== null) {
            $parentBlock = $this->blockHandler->loadBlock($persistenceBlock->parentId, Value::STATUS_DRAFT);
            if ($parentBlock->depth > 0 && !$parentBlock->isTranslatable) {
                throw new BadStateException('block', 'You can only enable translations if parent block is also translatable.');
            }
        }

        $persistenceLayout = $this->layoutHandler->loadLayout($persistenceBlock->layoutId, Value::STATUS_DRAFT);

        $updatedBlock = $this->transaction(
            function () use ($persistenceBlock, $persistenceLayout): PersistenceBlock {
                $updatedBlock = $this->blockHandler->updateBlock(
                    $persistenceBlock,
                    BlockUpdateStruct::fromArray(
                        [
                            'isTranslatable' => true,
                        ]
                    )
                );

                foreach ($persistenceLayout->availableLocales as $locale) {
                    if (!in_array($locale, $updatedBlock->availableLocales, true)) {
                        $updatedBlock = $this->blockHandler->createBlockTranslation(
                            $updatedBlock,
                            $locale,
                            $updatedBlock->mainLocale
                        );
                    }
                }

                return $updatedBlock;
            }
        );

        return $this->mapper->mapBlock($updatedBlock);
    }

    public function disableTranslations(Block $block): Block
    {
        if (!$block->isDraft()) {
            throw new BadStateException('block', 'You can only disable translations for draft blocks.');
        }

        $persistenceBlock = $this->blockHandler->loadBlock($block->getId(), Value::STATUS_DRAFT);

        if (!$persistenceBlock->isTranslatable) {
            throw new BadStateException('block', 'Block is not translatable.');
        }

        $updatedBlock = $this->transaction(
            function () use ($persistenceBlock): PersistenceBlock {
                return $this->internalDisableTranslations($persistenceBlock);
            }
        );

        return $this->mapper->mapBlock($updatedBlock);
    }

    public function deleteBlock(Block $block): void
    {
        if (!$block->isDraft()) {
            throw new BadStateException('block', 'Only draft blocks can be deleted.');
        }

        $persistenceBlock = $this->blockHandler->loadBlock($block->getId(), Value::STATUS_DRAFT);

        $this->transaction(
            function () use ($persistenceBlock): void {
                $this->blockHandler->deleteBlock($persistenceBlock);
            }
        );
    }

    public function newBlockCreateStruct(BlockDefinitionInterface $blockDefinition): APIBlockCreateStruct
    {
        return $this->structBuilder->newBlockCreateStruct($blockDefinition);
    }

    public function newBlockUpdateStruct(string $locale, ?Block $block = null): APIBlockUpdateStruct
    {
        return $this->structBuilder->newBlockUpdateStruct($locale, $block);
    }

    /**
     * Returns all blocks from provided input, with all untranslated blocks filtered out.
     */
    private function filterUntranslatedBlocks(iterable $blocks, ?array $locales, bool $useMainLocale): Generator
    {
        foreach ($blocks as $block) {
            try {
                yield $this->mapper->mapBlock($block, $locales, $useMainLocale);
            } catch (NotFoundException $e) {
                // Block does not have the translation, skip it
            }
        }
    }

    /**
     * Creates a block at specified target block and placeholder and position.
     *
     * If position is not provided, block is placed at the end of the placeholder.
     *
     * This is an internal method unifying creating a block in a zone and a parent block.
     */
    private function internalCreateBlock(
        APIBlockCreateStruct $blockCreateStruct,
        PersistenceBlock $targetBlock,
        string $placeholder,
        ?int $position = null
    ): Block {
        $persistenceLayout = $this->layoutHandler->loadLayout($targetBlock->layoutId, Value::STATUS_DRAFT);

        $createdBlock = $this->transaction(
            function () use ($blockCreateStruct, $persistenceLayout, $targetBlock, $placeholder, $position): PersistenceBlock {
                $blockDefinition = $blockCreateStruct->getDefinition();

                $createdBlock = $this->blockHandler->createBlock(
                    BlockCreateStruct::fromArray(
                        [
                            'status' => $targetBlock->status,
                            'position' => $position,
                            'definitionIdentifier' => $blockDefinition->getIdentifier(),
                            'viewType' => $blockCreateStruct->viewType,
                            'itemViewType' => $blockCreateStruct->itemViewType,
                            'name' => $blockCreateStruct->name,
                            'alwaysAvailable' => $blockCreateStruct->alwaysAvailable,
                            'isTranslatable' => $blockCreateStruct->isTranslatable,
                            'parameters' => iterator_to_array(
                                $this->parameterMapper->serializeValues(
                                    $blockDefinition,
                                    $blockCreateStruct->getParameterValues()
                                )
                            ),
                            'config' => iterator_to_array(
                                $this->configMapper->serializeValues(
                                    $blockCreateStruct->getConfigStructs(),
                                    $blockDefinition->getConfigDefinitions()
                                )
                            ),
                        ]
                    ),
                    $persistenceLayout,
                    $targetBlock,
                    $placeholder
                );

                $collectionCreateStructs = $blockCreateStruct->getCollectionCreateStructs();
                if (count($collectionCreateStructs) > 0) {
                    foreach ($collectionCreateStructs as $identifier => $collectionCreateStruct) {
                        $createdCollection = $this->collectionHandler->createCollection(
                            CollectionCreateStruct::fromArray(
                                [
                                    'status' => Value::STATUS_DRAFT,
                                    'offset' => $collectionCreateStruct->offset,
                                    'limit' => $collectionCreateStruct->limit,
                                    'alwaysAvailable' => $blockCreateStruct->alwaysAvailable,
                                    'isTranslatable' => $blockCreateStruct->isTranslatable,
                                    'mainLocale' => $persistenceLayout->mainLocale,
                                ]
                            )
                        );

                        if ($collectionCreateStruct->queryCreateStruct instanceof APIQueryCreateStruct) {
                            $queryType = $collectionCreateStruct->queryCreateStruct->getQueryType();
                            $this->collectionHandler->createQuery(
                                $createdCollection,
                                QueryCreateStruct::fromArray(
                                    [
                                        'type' => $queryType->getType(),
                                        'parameters' => iterator_to_array(
                                            $this->parameterMapper->serializeValues(
                                                $queryType,
                                                $collectionCreateStruct->queryCreateStruct->getParameterValues()
                                            )
                                        ),
                                    ]
                                )
                            );
                        }

                        if ($blockCreateStruct->isTranslatable) {
                            foreach ($persistenceLayout->availableLocales as $locale) {
                                if ($locale !== $persistenceLayout->mainLocale) {
                                    $createdCollection = $this->collectionHandler->createCollectionTranslation(
                                        $createdCollection,
                                        $locale,
                                        $createdCollection->mainLocale
                                    );
                                }
                            }
                        }

                        $this->blockHandler->createCollectionReference(
                            $createdBlock,
                            $createdCollection,
                            (string) $identifier
                        );
                    }
                }

                return $createdBlock;
            }
        );

        return $this->mapper->mapBlock($createdBlock);
    }

    /**
     * Moves a block to specified target block and placeholder and position.
     *
     * This is an internal method unifying moving a block to a zone and to a parent block.
     */
    private function internalMoveBlock(PersistenceBlock $block, PersistenceBlock $targetBlock, string $placeholder, int $position): PersistenceBlock
    {
        return $this->transaction(
            function () use ($block, $targetBlock, $placeholder, $position): PersistenceBlock {
                if ($block->parentId === $targetBlock->id && $block->placeholder === $placeholder) {
                    return $this->blockHandler->moveBlockToPosition($block, $position);
                }

                return $this->blockHandler->moveBlock(
                    $block,
                    $targetBlock,
                    $placeholder,
                    $position
                );
            }
        );
    }

    /**
     * Disables the translations on provided block and removes all translations
     * keeping only the main one.
     */
    private function internalDisableTranslations(PersistenceBlock $block): PersistenceBlock
    {
        $block = $this->blockHandler->updateBlock(
            $block,
            BlockUpdateStruct::fromArray(
                [
                    'isTranslatable' => false,
                ]
            )
        );

        foreach ($block->availableLocales as $locale) {
            if ($locale !== $block->mainLocale) {
                $block = $this->blockHandler->deleteBlockTranslation(
                    $block,
                    $locale
                );
            }
        }

        foreach ($this->blockHandler->loadChildBlocks($block) as $childBlock) {
            $this->internalDisableTranslations($childBlock);
        }

        return $block;
    }

    /**
     * Updates translations for specified block.
     *
     * This makes sure that untranslatable parameters are always kept in sync between all
     * available translations in the block. This means that if main translation is updated,
     * all other translations need to be updated too to reflect changes to untranslatable params,
     * and if any other translation is updated, it needs to take values of untranslatable params
     * from the main translation.
     */
    private function updateBlockTranslations(
        BlockDefinitionInterface $blockDefinition,
        PersistenceBlock $persistenceBlock,
        APIBlockUpdateStruct $blockUpdateStruct
    ): PersistenceBlock {
        if ($blockUpdateStruct->locale === $persistenceBlock->mainLocale) {
            $persistenceBlock = $this->blockHandler->updateBlockTranslation(
                $persistenceBlock,
                $blockUpdateStruct->locale,
                BlockTranslationUpdateStruct::fromArray(
                    [
                        'parameters' => iterator_to_array(
                            $this->parameterMapper->serializeValues(
                                $blockDefinition,
                                $blockUpdateStruct->getParameterValues(),
                                $persistenceBlock->parameters[$persistenceBlock->mainLocale]
                            )
                        ),
                    ]
                )
            );
        }

        $untranslatableParams = iterator_to_array(
            $this->parameterMapper->extractUntranslatableParameters(
                $blockDefinition,
                $persistenceBlock->parameters[$persistenceBlock->mainLocale]
            )
        );

        $localesToUpdate = [$blockUpdateStruct->locale];
        if ($persistenceBlock->mainLocale === $blockUpdateStruct->locale) {
            $localesToUpdate = $persistenceBlock->availableLocales;

            // Remove the main locale from the array, since we already updated that one
            $mainLocaleOffset = array_search($persistenceBlock->mainLocale, $persistenceBlock->availableLocales, true);
            if (is_int($mainLocaleOffset)) {
                array_splice($localesToUpdate, $mainLocaleOffset, 1);
            }
        }

        foreach ($localesToUpdate as $locale) {
            $params = $persistenceBlock->parameters[$locale];

            if ($locale === $blockUpdateStruct->locale) {
                $params = iterator_to_array(
                    $this->parameterMapper->serializeValues(
                        $blockDefinition,
                        $blockUpdateStruct->getParameterValues(),
                        $params
                    )
                );
            }

            $persistenceBlock = $this->blockHandler->updateBlockTranslation(
                $persistenceBlock,
                $locale,
                BlockTranslationUpdateStruct::fromArray(
                    [
                        'parameters' => $untranslatableParams + $params,
                    ]
                )
            );
        }

        return $persistenceBlock;
    }
}
