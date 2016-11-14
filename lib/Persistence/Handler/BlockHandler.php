<?php

namespace Netgen\BlockManager\Persistence\Handler;

use Netgen\BlockManager\Persistence\Values\BlockCreateStruct;
use Netgen\BlockManager\Persistence\Values\BlockUpdateStruct;
use Netgen\BlockManager\Persistence\Values\CollectionReferenceCreateStruct;
use Netgen\BlockManager\Persistence\Values\CollectionReferenceUpdateStruct;
use Netgen\BlockManager\Persistence\Values\Page\Block;
use Netgen\BlockManager\Persistence\Values\Page\CollectionReference;
use Netgen\BlockManager\Persistence\Values\Page\Layout;
use Netgen\BlockManager\Persistence\Values\Page\Zone;

interface BlockHandler
{
    /**
     * Loads a block with specified ID.
     *
     * @param int|string $blockId
     * @param int $status
     *
     * @throws \Netgen\BlockManager\Exception\NotFoundException If block with specified ID does not exist
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function loadBlock($blockId, $status);

    /**
     * Returns if block with specified ID exists.
     *
     * @param int|string $blockId
     * @param int $status
     *
     * @return bool
     */
    public function blockExists($blockId, $status);

    /**
     * Loads all blocks from zone with specified identifier.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Zone $zone
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block[]
     */
    public function loadZoneBlocks(Zone $zone);

    /**
     * Loads a collection reference.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $block
     * @param string $identifier
     *
     * @throws \Netgen\BlockManager\Exception\NotFoundException If collection reference with specified identifier does not exist
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\CollectionReference
     */
    public function loadCollectionReference(Block $block, $identifier);

    /**
     * Loads all collection references belonging to the provided block.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $block
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\CollectionReference[]
     */
    public function loadCollectionReferences(Block $block);

    /**
     * Creates a block in specified layout and zone.
     *
     * @param \Netgen\BlockManager\Persistence\Values\BlockCreateStruct $blockCreateStruct
     *
     * @throws \Netgen\BlockManager\Exception\BadStateException If provided position is out of range
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function createBlock(BlockCreateStruct $blockCreateStruct);

    /**
     * Creates the collection reference.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $block
     * @param \Netgen\BlockManager\Persistence\Values\CollectionReferenceCreateStruct $createStruct
     */
    public function createCollectionReference(Block $block, CollectionReferenceCreateStruct $createStruct);

    /**
     * Updates a block with specified ID.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $block
     * @param \Netgen\BlockManager\Persistence\Values\BlockUpdateStruct $blockUpdateStruct
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function updateBlock(Block $block, BlockUpdateStruct $blockUpdateStruct);

    /**
     * Updates a collection reference with specified identifier.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\CollectionReference $collectionReference
     * @param \Netgen\BlockManager\Persistence\Values\CollectionReferenceUpdateStruct $updateStruct
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\CollectionReference
     */
    public function updateCollectionReference(CollectionReference $collectionReference, CollectionReferenceUpdateStruct $updateStruct);

    /**
     * Copies a block to a specified layout.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $block
     * @param \Netgen\BlockManager\Persistence\Values\Page\Layout $layout
     * @param string $zoneIdentifier
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function copyBlock(Block $block, Layout $layout, $zoneIdentifier);

    /**
     * Copies all block collections to another block.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $block
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $targetBlock
     */
    public function copyBlockCollections(Block $block, Block $targetBlock);

    /**
     * Moves a block to specified position in the zone.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $block
     * @param int $position
     *
     * @throws \Netgen\BlockManager\Exception\BadStateException If provided position is out of range
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function moveBlock(Block $block, $position);

    /**
     * Moves a block to specified position in a specified zone.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $block
     * @param string $zoneIdentifier
     * @param int $position
     *
     * @throws \Netgen\BlockManager\Exception\BadStateException If provided position is out of range
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function moveBlockToZone(Block $block, $zoneIdentifier, $position);

    /**
     * Creates a new block status.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $block
     * @param int $newStatus
     */
    public function createBlockStatus(Block $block, $newStatus);

    /**
     * Creates a new status for all non shared collections in specified block.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $block
     * @param int $newStatus
     */
    public function createBlockCollectionsStatus(Block $block, $newStatus);

    /**
     * Deletes a block with specified ID.
     *
     * @param \Netgen\BlockManager\Persistence\Values\Page\Block $block
     */
    public function deleteBlock(Block $block);

    /**
     * Deletes blocks with specified IDs.
     *
     * @param array $blockIds
     * @param int $status
     */
    public function deleteBlocks(array $blockIds, $status = null);

    /**
     * Deletes block collections with specified block IDs.
     *
     * @param array $blockIds
     * @param int $status
     */
    public function deleteBlockCollections(array $blockIds, $status = null);
}
