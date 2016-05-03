<?php

namespace Netgen\BlockManager\Core\Persistence\Doctrine\Block;

use Netgen\BlockManager\API\Values\BlockCreateStruct;
use Netgen\BlockManager\API\Values\BlockUpdateStruct;
use Netgen\BlockManager\Core\Persistence\Doctrine\Helpers\ConnectionHelper;
use Netgen\BlockManager\Core\Persistence\Doctrine\Helpers\PositionHelper;
use Netgen\BlockManager\Persistence\Handler\Block as BlockHandlerInterface;
use Netgen\BlockManager\API\Exception\NotFoundException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

class Handler implements BlockHandlerInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \Netgen\BlockManager\Core\Persistence\Doctrine\Helpers\ConnectionHelper
     */
    protected $connectionHelper;

    /**
     * @var \Netgen\BlockManager\Core\Persistence\Doctrine\Helpers\PositionHelper
     */
    protected $positionHelper;

    /**
     * @var \Netgen\BlockManager\Core\Persistence\Doctrine\Block\Mapper
     */
    protected $mapper;

    /**
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Netgen\BlockManager\Core\Persistence\Doctrine\Helpers\ConnectionHelper $connectionHelper
     * @param \Netgen\BlockManager\Core\Persistence\Doctrine\Helpers\PositionHelper $positionHelper
     * @param \Netgen\BlockManager\Core\Persistence\Doctrine\Block\Mapper $mapper
     */
    public function __construct(
        Connection $connection,
        ConnectionHelper $connectionHelper,
        PositionHelper $positionHelper,
        Mapper $mapper
    ) {
        $this->connection = $connection;
        $this->connectionHelper = $connectionHelper;
        $this->positionHelper = $positionHelper;
        $this->mapper = $mapper;
    }

    /**
     * Loads a block with specified ID.
     *
     * @param int|string $blockId
     * @param int $status
     *
     * @throws \Netgen\BlockManager\API\Exception\NotFoundException If block with specified ID does not exist
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function loadBlock($blockId, $status)
    {
        $query = $this->createBlockSelectQuery();
        $query->where(
            $query->expr()->eq('id', ':id')
        )
        ->setParameter('id', $blockId, Type::INTEGER);

        $this->connectionHelper->applyStatusCondition($query, $status);

        $data = $query->execute()->fetchAll();
        if (empty($data)) {
            throw new NotFoundException('block', $blockId);
        }

        $data = $this->mapper->mapBlocks($data);

        return reset($data);
    }

    /**
     * Loads all blocks from zone with specified identifier.
     *
     * @param int|string $layoutId
     * @param string $zoneIdentifier
     * @param int $status
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block[]
     */
    public function loadZoneBlocks($layoutId, $zoneIdentifier, $status)
    {
        $query = $this->createBlockSelectQuery();
        $query->where(
                $query->expr()->andX(
                    $query->expr()->eq('layout_id', ':layout_id'),
                    $query->expr()->eq('zone_identifier', ':zone_identifier')
                )
            )
            ->setParameter('layout_id', $layoutId, Type::INTEGER)
            ->setParameter('zone_identifier', $zoneIdentifier, Type::STRING)
            ->orderBy('position', 'ASC');

        $this->connectionHelper->applyStatusCondition($query, $status);

        $data = $query->execute()->fetchAll();
        if (empty($data)) {
            return array();
        }

        return $this->mapper->mapBlocks($data);
    }

    /**
     * Creates a block in specified layout and zone.
     *
     * @param \Netgen\BlockManager\API\Values\BlockCreateStruct $blockCreateStruct
     * @param int|string $layoutId
     * @param string $zoneIdentifier
     * @param int $status
     * @param int $position
     *
     * @throws \Netgen\BlockManager\API\Exception\BadStateException If provided position is out of range
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function createBlock(BlockCreateStruct $blockCreateStruct, $layoutId, $zoneIdentifier, $status, $position = null)
    {
        $position = $this->positionHelper->createPosition(
            $this->getPositionHelperConditions(
                $layoutId,
                $zoneIdentifier,
                $status
            ),
            $position
        );

        $query = $this->createBlockInsertQuery(
            array(
                'status' => $status,
                'layout_id' => $layoutId,
                'zone_identifier' => $zoneIdentifier,
                'position' => $position,
                'definition_identifier' => $blockCreateStruct->definitionIdentifier,
                'view_type' => $blockCreateStruct->viewType,
                'name' => $blockCreateStruct->name !== null ? trim($blockCreateStruct->name) : '',
                'parameters' => $blockCreateStruct->getParameters(),
            )
        );

        $query->execute();

        $createdBlock = $this->loadBlock(
            (int)$this->connectionHelper->lastInsertId('ngbm_block'),
            $status
        );

        return $createdBlock;
    }

    /**
     * Updates a block with specified ID.
     *
     * @param int|string $blockId
     * @param int $status
     * @param \Netgen\BlockManager\API\Values\BlockUpdateStruct $blockUpdateStruct
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function updateBlock($blockId, $status, BlockUpdateStruct $blockUpdateStruct)
    {
        $block = $this->loadBlock($blockId, $status);

        $query = $this->connection->createQueryBuilder();
        $query
            ->update('ngbm_block')
            ->set('view_type', ':view_type')
            ->set('name', ':name')
            ->set('parameters', ':parameters')
            ->where(
                $query->expr()->eq('id', ':id')
            )
            ->setParameter('id', $blockId, Type::INTEGER)
            ->setParameter('view_type', $blockUpdateStruct->viewType !== null ? $blockUpdateStruct->viewType : $block->viewType, Type::STRING)
            ->setParameter('name', $blockUpdateStruct->name !== null ? trim($blockUpdateStruct->name) : $block->name, Type::STRING)
            ->setParameter('parameters', $blockUpdateStruct->getParameters(), Type::JSON_ARRAY);

        $this->connectionHelper->applyStatusCondition($query, $status);

        $query->execute();

        return $this->loadBlock($blockId, $status);
    }

    /**
     * Copies a block with specified ID to a zone with specified identifier.
     *
     * @param int|string $blockId
     * @param int $status
     * @param string $zoneIdentifier
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function copyBlock($blockId, $status, $zoneIdentifier)
    {
        $block = $this->loadBlock($blockId, $status);

        $query = $this->createBlockInsertQuery(
            array(
                'status' => $block->status,
                'layout_id' => $block->layoutId,
                'zone_identifier' => $zoneIdentifier,
                'position' => $this->positionHelper->getNextPosition(
                    $this->getPositionHelperConditions(
                        $block->layoutId,
                        $zoneIdentifier,
                        $status
                    )
                ),
                'definition_identifier' => $block->definitionIdentifier,
                'view_type' => $block->viewType,
                'name' => $block->name,
                'parameters' => $block->parameters,
            )
        );

        $query->execute();

        return $this->loadBlock(
            (int)$this->connectionHelper->lastInsertId('ngbm_block'),
            $block->status
        );
    }

    /**
     * Moves a block to specified position in the zone.
     *
     * @param int|string $blockId
     * @param int $status
     * @param int $position
     *
     * @throws \Netgen\BlockManager\API\Exception\BadStateException If provided position is out of range
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function moveBlock($blockId, $status, $position)
    {
        $block = $this->loadBlock($blockId, $status);

        $position = $this->positionHelper->moveToPosition(
            $this->getPositionHelperConditions(
                $block->layoutId,
                $block->zoneIdentifier,
                $status
            ),
            $block->position,
            $position
        );

        $query = $this->connection->createQueryBuilder();

        $query
            ->update('ngbm_block')
            ->set('position', ':position')
            ->where(
                $query->expr()->eq('id', ':id')
            )
            ->setParameter('id', $block->id, Type::INTEGER)
            ->setParameter('position', $position, Type::INTEGER);

        $this->connectionHelper->applyStatusCondition($query, $status);

        $query->execute();

        $movedBlock = $this->loadBlock($blockId, $status);

        return $movedBlock;
    }

    /**
     * Moves a block to specified position in a specified zone.
     *
     * @param int|string $blockId
     * @param int $status
     * @param string $zoneIdentifier
     * @param int $position
     *
     * @throws \Netgen\BlockManager\API\Exception\BadStateException If provided position is out of range
     *
     * @return \Netgen\BlockManager\Persistence\Values\Page\Block
     */
    public function moveBlockToZone($blockId, $status, $zoneIdentifier, $position)
    {
        $block = $this->loadBlock($blockId, $status);

        $position = $this->positionHelper->createPosition(
            $this->getPositionHelperConditions(
                $block->layoutId,
                $zoneIdentifier,
                $status
            ),
            $position
        );

        $query = $this->connection->createQueryBuilder();

        $query
            ->update('ngbm_block')
            ->set('zone_identifier', ':zone_identifier')
            ->set('position', ':position')
            ->where(
                $query->expr()->eq('id', ':id')
            )
            ->setParameter('id', $block->id, Type::INTEGER)
            ->setParameter('zone_identifier', $zoneIdentifier, Type::STRING)
            ->setParameter('position', $position, Type::INTEGER);

        $this->connectionHelper->applyStatusCondition($query, $status);

        $query->execute();

        $this->positionHelper->removePosition(
            $this->getPositionHelperConditions(
                $block->layoutId,
                $block->zoneIdentifier,
                $status
            ),
            $block->position
        );

        $movedBlock = $this->loadBlock($blockId, $status);

        return $movedBlock;
    }

    /**
     * Deletes a block with specified ID.
     *
     * @param int|string $blockId
     * @param int $status
     */
    public function deleteBlock($blockId, $status)
    {
        $block = $this->loadBlock($blockId, $status);

        $query = $this->connection->createQueryBuilder();

        $query->delete('ngbm_block')
            ->where(
                $query->expr()->eq('id', ':id')
            )
            ->setParameter('id', $blockId, Type::INTEGER);

        $this->connectionHelper->applyStatusCondition($query, $status);

        $query->execute();

        $this->positionHelper->removePosition(
            $this->getPositionHelperConditions(
                $block->layoutId,
                $block->zoneIdentifier,
                $status
            ),
            $block->position
        );
    }

    /**
     * Returns if collection with provided ID already exists in the block.
     *
     * @param int|string $blockId
     * @param int $status
     * @param int|string $collectionId
     *
     * @return bool
     */
    public function collectionExists($blockId, $status, $collectionId)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('count(*) AS count')
            ->from('ngbm_block_collection')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('block_id', ':block_id'),
                    $query->expr()->eq('collection_id', ':collection_id')
                )
            )
            ->setParameter('block_id', $blockId, Type::INTEGER)
            ->setParameter('collection_id', $collectionId, Type::INTEGER);

        $this->connectionHelper->applyStatusCondition($query, $status);

        $data = $query->execute()->fetchAll();

        return isset($data[0]['count']) && $data[0]['count'] > 0;
    }

    /**
     * Returns if provided collection identifier already exists in the block.
     *
     * @param int|string $blockId
     * @param int $status
     * @param string $identifier
     *
     * @return bool
     */
    public function collectionIdentifierExists($blockId, $status, $identifier)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('count(*) AS count')
            ->from('ngbm_block_collection')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('block_id', ':block_id'),
                    $query->expr()->eq('identifier', ':identifier')
                )
            )
            ->setParameter('block_id', $blockId, Type::INTEGER)
            ->setParameter('identifier', $identifier, Type::STRING);

        $this->connectionHelper->applyStatusCondition($query, $status);

        $data = $query->execute()->fetchAll();

        return isset($data[0]['count']) && $data[0]['count'] > 0;
    }

    /**
     * Adds the collection to the block.
     *
     * @param int|string $blockId
     * @param int $status
     * @param int|string $collectionId
     * @param string $identifier
     */
    public function addCollectionToBlock($blockId, $status, $collectionId, $identifier)
    {
        $query = $this->connection->createQueryBuilder();

        $query->insert('ngbm_block_collection')
            ->values(
                array(
                    'block_id' => ':block_id',
                    'status' => ':status',
                    'collection_id' => ':collection_id',
                    'identifier' => ':identifier',
                )
            )
            ->setParameter('block_id', $blockId, Type::INTEGER)
            ->setParameter('status', $status, Type::INTEGER)
            ->setParameter('collection_id', $collectionId, Type::INTEGER)
            ->setParameter('identifier', $identifier, Type::INTEGER);

        $query->execute();
    }

    /**
     * Removes the collection from the block.
     *
     * @param int|string $blockId
     * @param int $status
     * @param int|string $collectionId
     */
    public function removeCollectionFromBlock($blockId, $status, $collectionId)
    {
        $query = $this->connection->createQueryBuilder();

        $query->delete('ngbm_block_collection')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('block_id', ':block_id'),
                    $query->expr()->eq('collection_id', ':collection_id')
                )
            )
            ->setParameter('block_id', $blockId, Type::INTEGER)
            ->setParameter('collection_id', $collectionId, Type::INTEGER);

        $this->connectionHelper->applyStatusCondition($query, $status);

        $query->execute();
    }

    /**
     * Builds and returns a block database SELECT query.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function createBlockSelectQuery()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('id', 'status', 'layout_id', 'zone_identifier', 'position', 'definition_identifier', 'view_type', 'name', 'parameters')
            ->from('ngbm_block');

        return $query;
    }

    /**
     * Builds and returns a block database INSERT query.
     *
     * @param array $parameters
     * @param int $blockId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function createBlockInsertQuery(array $parameters, $blockId = null)
    {
        return $this->connection->createQueryBuilder()
            ->insert('ngbm_block')
            ->values(
                array(
                    'id' => ':id',
                    'status' => ':status',
                    'layout_id' => ':layout_id',
                    'zone_identifier' => ':zone_identifier',
                    'position' => ':position',
                    'definition_identifier' => ':definition_identifier',
                    'view_type' => ':view_type',
                    'name' => ':name',
                    'parameters' => ':parameters',
                )
            )
            ->setValue(
                'id',
                $blockId !== null ? (int)$blockId : $this->connectionHelper->getAutoIncrementValue('ngbm_block')
            )
            ->setParameter('status', $parameters['status'], Type::INTEGER)
            ->setParameter('layout_id', $parameters['layout_id'], Type::INTEGER)
            ->setParameter('zone_identifier', $parameters['zone_identifier'], Type::STRING)
            ->setParameter('position', $parameters['position'], Type::INTEGER)
            ->setParameter('definition_identifier', $parameters['definition_identifier'], Type::STRING)
            ->setParameter('view_type', $parameters['view_type'], Type::STRING)
            ->setParameter('name', trim($parameters['name']), Type::STRING)
            ->setParameter('parameters', $parameters['parameters'], Type::JSON_ARRAY);
    }

    /**
     * Builds the condition array that will be used with position helper.
     *
     * @param int|string $layoutId
     * @param string $zoneIdentifier
     * @param int $status
     *
     * @return array
     */
    protected function getPositionHelperConditions($layoutId, $zoneIdentifier, $status)
    {
        return array(
            'table' => 'ngbm_block',
            'column' => 'position',
            'conditions' => array(
                'layout_id' => $layoutId,
                'zone_identifier' => $zoneIdentifier,
                'status' => $status,
            ),
        );
    }
}
