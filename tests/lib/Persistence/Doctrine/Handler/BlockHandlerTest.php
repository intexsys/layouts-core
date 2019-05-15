<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Persistence\Doctrine\Handler;

use Doctrine\DBAL\Types\Type;
use Netgen\Layouts\Exception\BadStateException;
use Netgen\Layouts\Exception\NotFoundException;
use Netgen\Layouts\Persistence\Values\Block\Block;
use Netgen\Layouts\Persistence\Values\Block\BlockCreateStruct;
use Netgen\Layouts\Persistence\Values\Block\BlockTranslationUpdateStruct;
use Netgen\Layouts\Persistence\Values\Block\BlockUpdateStruct;
use Netgen\Layouts\Persistence\Values\Block\CollectionReference;
use Netgen\Layouts\Persistence\Values\Value;
use Netgen\Layouts\Tests\Persistence\Doctrine\TestCaseTrait;
use Netgen\Layouts\Tests\TestCase\ExportObjectTrait;
use Netgen\Layouts\Tests\TestCase\UuidGeneratorTrait;
use PHPUnit\Framework\TestCase;

final class BlockHandlerTest extends TestCase
{
    use TestCaseTrait;
    use ExportObjectTrait;
    use UuidGeneratorTrait;

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

    public function setUp(): void
    {
        $this->createDatabase();

        $this->blockHandler = $this->createBlockHandler();
        $this->layoutHandler = $this->createLayoutHandler();
        $this->collectionHandler = $this->createCollectionHandler();
    }

    /**
     * Tears down the tests.
     */
    public function tearDown(): void
    {
        $this->closeDatabase();
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::__construct
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::loadBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::__construct
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::getBlockSelectQuery
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadBlockData
     */
    public function testLoadBlock(): void
    {
        $block = $this->blockHandler->loadBlock(31, Value::STATUS_PUBLISHED);

        self::assertSame(
            [
                'id' => 31,
                'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/31/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 3,
                    ],
                    'hr' => [
                        'number_of_columns' => 3,
                    ],
                ],
                'config' => [],
                'viewType' => 'grid',
                'itemViewType' => 'standard_with_intro',
                'name' => 'My published block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_PUBLISHED,
            ],
            $this->exportObject($block)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::loadBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadBlockData
     */
    public function testLoadBlockThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Could not find block with identifier "999999"');

        $this->blockHandler->loadBlock(999999, Value::STATUS_PUBLISHED);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::blockExists
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::blockExists
     */
    public function testBlockExists(): void
    {
        self::assertTrue($this->blockHandler->blockExists(31, Value::STATUS_PUBLISHED));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::blockExists
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::blockExists
     */
    public function testBlockNotExists(): void
    {
        self::assertFalse($this->blockHandler->blockExists(999999, Value::STATUS_PUBLISHED));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::blockExists
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::blockExists
     */
    public function testBlockNotExistsInStatus(): void
    {
        self::assertFalse($this->blockHandler->blockExists(36, Value::STATUS_PUBLISHED));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::loadLayoutBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadLayoutBlocksData
     */
    public function testLoadLayoutBlocks(): void
    {
        $blocks = $this->blockHandler->loadLayoutBlocks(
            $this->layoutHandler->loadLayout(1, Value::STATUS_PUBLISHED)
        );

        self::assertCount(7, $blocks);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::loadZoneBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadAllChildBlocksData
     */
    public function testLoadZoneBlocks(): void
    {
        $blocks = $this->blockHandler->loadZoneBlocks(
            $this->layoutHandler->loadZone(1, Value::STATUS_PUBLISHED, 'right')
        );

        self::assertCount(3, $blocks);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::loadChildBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadChildBlocksData
     */
    public function testLoadChildBlocks(): void
    {
        $blocks = $this->blockHandler->loadChildBlocks(
            $this->blockHandler->loadBlock(3, Value::STATUS_PUBLISHED)
        );

        self::assertContainsOnlyInstancesOf(Block::class, $blocks);

        self::assertSame(
            [
                [
                    'id' => 31,
                    'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'depth' => 1,
                    'path' => '/3/31/',
                    'parentId' => 3,
                    'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                    'placeholder' => 'root',
                    'position' => 0,
                    'definitionIdentifier' => 'list',
                    'parameters' => [
                        'en' => [
                            'number_of_columns' => 3,
                        ],
                        'hr' => [
                            'number_of_columns' => 3,
                        ],
                    ],
                    'config' => [],
                    'viewType' => 'grid',
                    'itemViewType' => 'standard_with_intro',
                    'name' => 'My published block',
                    'isTranslatable' => true,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en', 'hr'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_PUBLISHED,
                ],
                [
                    'id' => 35,
                    'uuid' => 'c2a30ea3-95ef-55b0-a584-fbcfd93cec9e',
                    'layoutId' => 1,
                    'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                    'depth' => 1,
                    'path' => '/3/35/',
                    'parentId' => 3,
                    'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                    'placeholder' => 'root',
                    'position' => 1,
                    'definitionIdentifier' => 'list',
                    'parameters' => [
                        'en' => [
                            'number_of_columns' => 3,
                        ],
                    ],
                    'config' => [],
                    'viewType' => 'grid',
                    'itemViewType' => 'standard',
                    'name' => 'My fourth block',
                    'isTranslatable' => false,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_PUBLISHED,
                ],
            ],
            $this->exportObjectList($blocks)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::loadChildBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadChildBlocksData
     */
    public function testLoadChildBlocksInPlaceholder(): void
    {
        $blocks = $this->blockHandler->loadChildBlocks(
            $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT),
            'left'
        );

        self::assertContainsOnlyInstancesOf(Block::class, $blocks);

        self::assertSame(
            [
                [
                    'id' => 37,
                    'uuid' => '129f51de-a535-5094-8517-45d672e06302',
                    'layoutId' => 2,
                    'layoutUuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                    'depth' => 2,
                    'path' => '/7/33/37/',
                    'parentId' => 33,
                    'parentUuid' => 'e666109d-f1db-5fd5-97fa-346f50e9ae59',
                    'placeholder' => 'left',
                    'position' => 0,
                    'definitionIdentifier' => 'text',
                    'parameters' => [
                        'en' => [
                            'content' => 'Text',
                        ],
                    ],
                    'config' => [],
                    'viewType' => 'text',
                    'itemViewType' => 'standard',
                    'name' => 'My seventh block',
                    'isTranslatable' => false,
                    'mainLocale' => 'en',
                    'availableLocales' => ['en'],
                    'alwaysAvailable' => true,
                    'status' => Value::STATUS_DRAFT,
                ],
            ],
            $this->exportObjectList($blocks)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::loadChildBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadChildBlocksData
     */
    public function testLoadChildBlocksWithUnknownPlaceholder(): void
    {
        self::assertEmpty(
            $this->blockHandler->loadChildBlocks(
                $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT),
                'unknown'
            )
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::loadCollectionReference
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadCollectionReferencesData
     */
    public function testLoadCollectionReference(): void
    {
        $reference = $this->blockHandler->loadCollectionReference(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'default'
        );

        self::assertSame(
            [
                'blockId' => 31,
                'blockStatus' => Value::STATUS_DRAFT,
                'collectionId' => 1,
                'collectionStatus' => Value::STATUS_DRAFT,
                'identifier' => 'default',
            ],
            $this->exportObject($reference)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::loadCollectionReference
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadCollectionReferencesData
     */
    public function testLoadCollectionReferenceThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Could not find collection reference with identifier "non_existing"');

        $this->blockHandler->loadCollectionReference(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'non_existing'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::loadCollectionReferences
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadCollectionReferencesData
     */
    public function testLoadCollectionReferences(): void
    {
        $references = $this->blockHandler->loadCollectionReferences(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT)
        );

        self::assertContainsOnlyInstancesOf(CollectionReference::class, $references);

        self::assertSame(
            [
                [
                    'blockId' => 31,
                    'blockStatus' => Value::STATUS_DRAFT,
                    'collectionId' => 1,
                    'collectionStatus' => Value::STATUS_DRAFT,
                    'identifier' => 'default',
                ],
                [
                    'blockId' => 31,
                    'blockStatus' => Value::STATUS_DRAFT,
                    'collectionId' => 3,
                    'collectionStatus' => Value::STATUS_DRAFT,
                    'identifier' => 'featured',
                ],
            ],
            $this->exportObjectList($references)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCreateBlock(): void
    {
        $blockCreateStruct = new BlockCreateStruct();
        $blockCreateStruct->isTranslatable = true;
        $blockCreateStruct->alwaysAvailable = true;
        $blockCreateStruct->status = Value::STATUS_DRAFT;
        $blockCreateStruct->definitionIdentifier = 'new_block';
        $blockCreateStruct->position = 0;
        $blockCreateStruct->viewType = 'large';
        $blockCreateStruct->itemViewType = 'standard';
        $blockCreateStruct->name = 'My block';

        $blockCreateStruct->parameters = [
            'a_param' => 'A value',
        ];

        $blockCreateStruct->config = [
            'config_param' => 'Config value',
        ];

        $createdBlock = $this->withUuids(
            function () use ($blockCreateStruct): Block {
                return $this->blockHandler->createBlock(
                    $blockCreateStruct,
                    $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT),
                    $this->blockHandler->loadBlock(3, Value::STATUS_DRAFT),
                    'root'
                );
            },
            ['f06f245a-f951-52c8-bfa3-84c80154eadc']
        );

        self::assertSame(
            [
                'id' => 39,
                'uuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/39/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'new_block',
                'parameters' => [
                    'en' => [
                        'a_param' => 'A value',
                    ],
                    'hr' => [
                        'a_param' => 'A value',
                    ],
                ],
                'config' => [
                    'config_param' => 'Config value',
                ],
                'viewType' => 'large',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($createdBlock)
        );

        $secondBlock = $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);
        self::assertSame(1, $secondBlock->position);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlockTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCreateBlockTranslation(): void
    {
        $block = $this->blockHandler->createBlockTranslation(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'de',
            'en'
        );

        self::assertSame(
            [
                'id' => 31,
                'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/31/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                    'de' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr', 'de'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($block)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlockTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCreateBlockTranslationWithNonMainSourceLocale(): void
    {
        $block = $this->blockHandler->createBlockTranslation(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'de',
            'hr'
        );

        self::assertSame(
            [
                'id' => 31,
                'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/31/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                    'de' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr', 'de'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($block)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlockTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCreateBlockTranslationThrowsBadStateExceptionWithExistingLocale(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "locale" has an invalid state. Block already has the provided locale.');

        $this->blockHandler->createBlockTranslation(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'en',
            'hr'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlockTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCreateBlockTranslationThrowsBadStateExceptionWithNonExistingSourceLocale(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "locale" has an invalid state. Block does not have the provided source locale.');

        $this->blockHandler->createBlockTranslation(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'de',
            'fr'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     */
    public function testCreateBlockWithNoParent(): void
    {
        $blockCreateStruct = new BlockCreateStruct();
        $blockCreateStruct->isTranslatable = false;
        $blockCreateStruct->alwaysAvailable = true;
        $blockCreateStruct->status = Value::STATUS_DRAFT;
        $blockCreateStruct->definitionIdentifier = 'new_block';
        $blockCreateStruct->position = 0;
        $blockCreateStruct->viewType = 'large';
        $blockCreateStruct->itemViewType = 'standard';
        $blockCreateStruct->name = 'My block';

        $blockCreateStruct->parameters = [
            'a_param' => 'A value',
        ];

        $blockCreateStruct->config = [
            'config_param' => 'Config value',
        ];

        $block = $this->withUuids(
            function () use ($blockCreateStruct): Block {
                return $this->blockHandler->createBlock(
                    $blockCreateStruct,
                    $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT)
                );
            },
            ['f06f245a-f951-52c8-bfa3-84c80154eadc']
        );

        self::assertSame(
            [
                'id' => 39,
                'uuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 0,
                'path' => '/39/',
                'parentId' => null,
                'parentUuid' => null,
                'placeholder' => null,
                'position' => null,
                'definitionIdentifier' => 'new_block',
                'parameters' => [
                    'en' => [
                        'a_param' => 'A value',
                    ],
                ],
                'config' => [
                    'config_param' => 'Config value',
                ],
                'viewType' => 'large',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => false,
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($block)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     */
    public function testCreateBlockWithNoPosition(): void
    {
        $blockCreateStruct = new BlockCreateStruct();
        $blockCreateStruct->isTranslatable = true;
        $blockCreateStruct->alwaysAvailable = true;
        $blockCreateStruct->status = Value::STATUS_DRAFT;
        $blockCreateStruct->definitionIdentifier = 'new_block';
        $blockCreateStruct->viewType = 'large';
        $blockCreateStruct->itemViewType = 'standard';
        $blockCreateStruct->name = 'My block';

        $blockCreateStruct->parameters = [
            'a_param' => 'A value',
        ];

        $blockCreateStruct->config = [
            'config' => 'Config value',
        ];

        $block = $this->withUuids(
            function () use ($blockCreateStruct): Block {
                return $this->blockHandler->createBlock(
                    $blockCreateStruct,
                    $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT),
                    $this->blockHandler->loadBlock(3, Value::STATUS_DRAFT),
                    'root'
                );
            },
            ['f06f245a-f951-52c8-bfa3-84c80154eadc']
        );

        self::assertSame(
            [
                'id' => 39,
                'uuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/39/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 2,
                'definitionIdentifier' => 'new_block',
                'parameters' => [
                    'en' => [
                        'a_param' => 'A value',
                    ],
                    'hr' => [
                        'a_param' => 'A value',
                    ],
                ],
                'config' => [
                    'config' => 'Config value',
                ],
                'viewType' => 'large',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($block)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     */
    public function testCreateBlockThrowsBadStateExceptionOnTargetBlockInDifferentLayout(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "targetBlock" has an invalid state. Target block is not in the provided layout.');

        $blockCreateStruct = new BlockCreateStruct();
        $blockCreateStruct->isTranslatable = true;
        $blockCreateStruct->alwaysAvailable = true;
        $blockCreateStruct->status = Value::STATUS_DRAFT;
        $blockCreateStruct->definitionIdentifier = 'new_block';
        $blockCreateStruct->position = 0;
        $blockCreateStruct->viewType = 'large';
        $blockCreateStruct->itemViewType = 'standard';
        $blockCreateStruct->name = 'My block';

        $blockCreateStruct->parameters = [
            'a_param' => 'A value',
        ];

        $this->blockHandler->createBlock(
            $blockCreateStruct,
            $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(5, Value::STATUS_DRAFT),
            'root'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     */
    public function testCreateBlockThrowsBadStateExceptionOnNegativePosition(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "position" has an invalid state. Position cannot be negative.');

        $blockCreateStruct = new BlockCreateStruct();
        $blockCreateStruct->isTranslatable = true;
        $blockCreateStruct->alwaysAvailable = true;
        $blockCreateStruct->status = Value::STATUS_DRAFT;
        $blockCreateStruct->definitionIdentifier = 'new_block';
        $blockCreateStruct->position = -5;
        $blockCreateStruct->viewType = 'large';
        $blockCreateStruct->itemViewType = 'standard';
        $blockCreateStruct->name = 'My block';

        $blockCreateStruct->parameters = [
            'a_param' => 'A value',
        ];

        $this->blockHandler->createBlock(
            $blockCreateStruct,
            $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(3, Value::STATUS_DRAFT),
            'root'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     */
    public function testCreateBlockThrowsBadStateExceptionOnTooLargePosition(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "position" has an invalid state. Position is out of range.');

        $blockCreateStruct = new BlockCreateStruct();
        $blockCreateStruct->isTranslatable = true;
        $blockCreateStruct->alwaysAvailable = true;
        $blockCreateStruct->status = Value::STATUS_DRAFT;
        $blockCreateStruct->definitionIdentifier = 'new_block';
        $blockCreateStruct->position = 9999;
        $blockCreateStruct->viewType = 'large';
        $blockCreateStruct->itemViewType = 'standard';
        $blockCreateStruct->name = 'My block';

        $blockCreateStruct->parameters = [
            'a_param' => 'A value',
        ];

        $this->blockHandler->createBlock(
            $blockCreateStruct,
            $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(3, Value::STATUS_DRAFT),
            'root'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createCollectionReference
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createCollectionReference
     */
    public function testCreateCollectionReference(): void
    {
        $block = $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);
        $collection = $this->collectionHandler->loadCollection(2, Value::STATUS_PUBLISHED);

        $reference = $this->blockHandler->createCollectionReference(
            $block,
            $collection,
            'new'
        );

        self::assertSame(
            [
                'blockId' => $block->id,
                'blockStatus' => $block->status,
                'collectionId' => $collection->id,
                'collectionStatus' => $collection->status,
                'identifier' => 'new',
            ],
            $this->exportObject($reference)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::updateBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::updateBlock
     */
    public function testUpdateBlock(): void
    {
        $blockUpdateStruct = new BlockUpdateStruct();
        $blockUpdateStruct->viewType = 'large';
        $blockUpdateStruct->itemViewType = 'new';
        $blockUpdateStruct->name = 'Updated name';
        $blockUpdateStruct->config = ['config'];
        $blockUpdateStruct->isTranslatable = false;
        $blockUpdateStruct->alwaysAvailable = false;
        $blockUpdateStruct->config = ['config'];

        $updatedBlock = $this->blockHandler->updateBlock(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            $blockUpdateStruct
        );

        self::assertSame(
            [
                'id' => 31,
                'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/31/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => ['config'],
                'viewType' => 'large',
                'itemViewType' => 'new',
                'name' => 'Updated name',
                'isTranslatable' => false,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => false,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($updatedBlock)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::updateBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::updateBlock
     */
    public function testUpdateBlockWithDefaultValues(): void
    {
        $blockUpdateStruct = new BlockUpdateStruct();

        $updatedBlock = $this->blockHandler->updateBlock(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            $blockUpdateStruct
        );

        self::assertSame(
            [
                'id' => 31,
                'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/31/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($updatedBlock)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::updateBlockTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::updateBlockTranslation
     */
    public function testUpdateBlockTranslation(): void
    {
        $translationUpdateStruct = new BlockTranslationUpdateStruct();

        $translationUpdateStruct->parameters = [
            'number_of_columns' => 4,
            'some_param' => 'Some value',
        ];

        $updatedBlock = $this->blockHandler->updateBlockTranslation(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'en',
            $translationUpdateStruct
        );

        self::assertSame(
            [
                'id' => 31,
                'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/31/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 4,
                        'some_param' => 'Some value',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($updatedBlock)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::updateBlockTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::updateBlockTranslation
     */
    public function testUpdateBlockTranslationWithDefaultValues(): void
    {
        $translationUpdateStruct = new BlockTranslationUpdateStruct();

        $updatedBlock = $this->blockHandler->updateBlockTranslation(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'en',
            $translationUpdateStruct
        );

        self::assertSame(
            [
                'id' => 31,
                'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/31/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($updatedBlock)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::updateBlockTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::updateBlockTranslation
     */
    public function testUpdateBlockTranslationThrowsBadStateExceptionWithNonExistingLocale(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "locale" has an invalid state. Block does not have the provided locale.');

        $this->blockHandler->updateBlockTranslation(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'de',
            new BlockTranslationUpdateStruct()
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::setMainTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::updateBlock
     */
    public function testSetMainTranslation(): void
    {
        $block = $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);
        $block = $this->blockHandler->setMainTranslation($block, 'hr');

        self::assertSame('hr', $block->mainLocale);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::setMainTranslation
     */
    public function testSetMainTranslationThrowsBadStateExceptionWithNonExistingLocale(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "mainLocale" has an invalid state. Block does not have the provided locale.');

        $block = $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);
        $this->blockHandler->setMainTranslation($block, 'de');
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlockCollections
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCopyBlock(): void
    {
        $copiedBlock = $this->withUuids(
            function (): Block {
                return $this->blockHandler->copyBlock(
                    $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
                    $this->blockHandler->loadBlock(3, Value::STATUS_DRAFT),
                    'root'
                );
            },
            [
                'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'cf29cf92-0294-5581-abdb-58d11978186b',
                '805895b2-6292-5243-a0c0-06a6ec0e28a2',
                '76b05000-33ac-53f7-adfd-c91936d1f6b1',
                '6dc13cc7-fd76-5e41-8b0c-1ed93ece7fcf',
                '70fe4f3a-7e9d-5a1f-9e6a-b038c06ea117',
                '3a3aa59a-76fe-532f-8a03-c04a93d803f6',
                'f08717e5-5910-574d-b976-03d877c4729b',
                'e804ebd6-dc99-53bb-85d5-196d68933761',
                '910f4fe2-97b0-5599-8a45-8fb8a8e0ca6d',
            ]
        );

        self::assertSame(
            [
                'id' => 39,
                'uuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/39/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 2,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($copiedBlock)
        );

        self::assertSame(
            [
                [
                    'blockId' => 39,
                    'blockStatus' => Value::STATUS_DRAFT,
                    'collectionId' => 7,
                    'collectionStatus' => Value::STATUS_DRAFT,
                    'identifier' => 'default',
                ],
                [
                    'blockId' => 39,
                    'blockStatus' => Value::STATUS_DRAFT,
                    'collectionId' => 8,
                    'collectionStatus' => Value::STATUS_DRAFT,
                    'identifier' => 'featured',
                ],
            ],
            $this->exportObjectList(
                $this->blockHandler->loadCollectionReferences($copiedBlock)
            )
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlockCollections
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCopyBlockWithPosition(): void
    {
        $copiedBlock = $this->withUuids(
            function (): Block {
                return $this->blockHandler->copyBlock(
                    $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
                    $this->blockHandler->loadBlock(3, Value::STATUS_DRAFT),
                    'root',
                    1
                );
            },
            [
                'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'cf29cf92-0294-5581-abdb-58d11978186b',
                '805895b2-6292-5243-a0c0-06a6ec0e28a2',
                '76b05000-33ac-53f7-adfd-c91936d1f6b1',
                '6dc13cc7-fd76-5e41-8b0c-1ed93ece7fcf',
                '70fe4f3a-7e9d-5a1f-9e6a-b038c06ea117',
                '3a3aa59a-76fe-532f-8a03-c04a93d803f6',
                'f08717e5-5910-574d-b976-03d877c4729b',
                'e804ebd6-dc99-53bb-85d5-196d68933761',
                '910f4fe2-97b0-5599-8a45-8fb8a8e0ca6d',
            ]
        );

        self::assertSame(
            [
                'id' => 39,
                'uuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/39/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 1,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($copiedBlock)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlockCollections
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCopyBlockWithSamePosition(): void
    {
        $copiedBlock = $this->withUuids(
            function (): Block {
                return $this->blockHandler->copyBlock(
                    $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
                    $this->blockHandler->loadBlock(3, Value::STATUS_DRAFT),
                    'root',
                    0
                );
            },
            [
                'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'cf29cf92-0294-5581-abdb-58d11978186b',
                '805895b2-6292-5243-a0c0-06a6ec0e28a2',
                '76b05000-33ac-53f7-adfd-c91936d1f6b1',
                '6dc13cc7-fd76-5e41-8b0c-1ed93ece7fcf',
                '70fe4f3a-7e9d-5a1f-9e6a-b038c06ea117',
                '3a3aa59a-76fe-532f-8a03-c04a93d803f6',
                'f08717e5-5910-574d-b976-03d877c4729b',
                'e804ebd6-dc99-53bb-85d5-196d68933761',
                '910f4fe2-97b0-5599-8a45-8fb8a8e0ca6d',
            ]
        );

        self::assertSame(
            [
                'id' => 39,
                'uuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/39/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($copiedBlock)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlockCollections
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCopyBlockWithLastPosition(): void
    {
        $copiedBlock = $this->withUuids(
            function (): Block {
                return $this->blockHandler->copyBlock(
                    $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
                    $this->blockHandler->loadBlock(3, Value::STATUS_DRAFT),
                    'root',
                    2
                );
            },
            [
                'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'cf29cf92-0294-5581-abdb-58d11978186b',
                '805895b2-6292-5243-a0c0-06a6ec0e28a2',
                '76b05000-33ac-53f7-adfd-c91936d1f6b1',
                '6dc13cc7-fd76-5e41-8b0c-1ed93ece7fcf',
                '70fe4f3a-7e9d-5a1f-9e6a-b038c06ea117',
                '3a3aa59a-76fe-532f-8a03-c04a93d803f6',
                'f08717e5-5910-574d-b976-03d877c4729b',
                'e804ebd6-dc99-53bb-85d5-196d68933761',
                '910f4fe2-97b0-5599-8a45-8fb8a8e0ca6d',
            ]
        );

        self::assertSame(
            [
                'id' => 39,
                'uuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/39/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 2,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($copiedBlock)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlockCollections
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCopyBlockWithLowerPosition(): void
    {
        $copiedBlock = $this->withUuids(
            function (): Block {
                return $this->blockHandler->copyBlock(
                    $this->blockHandler->loadBlock(35, Value::STATUS_DRAFT),
                    $this->blockHandler->loadBlock(3, Value::STATUS_DRAFT),
                    'root',
                    0
                );
            },
            [
                'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'cf29cf92-0294-5581-abdb-58d11978186b',
                '76b05000-33ac-53f7-adfd-c91936d1f6b1',
                '6dc13cc7-fd76-5e41-8b0c-1ed93ece7fcf',
                '70fe4f3a-7e9d-5a1f-9e6a-b038c06ea117',
                '3a3aa59a-76fe-532f-8a03-c04a93d803f6',
            ]
        );

        self::assertSame(
            [
                'id' => 39,
                'uuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/39/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 3,
                    ],
                ],
                'config' => [],
                'viewType' => 'grid',
                'itemViewType' => 'standard',
                'name' => 'My fourth block',
                'isTranslatable' => false,
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($copiedBlock)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlock
     */
    public function testCopyBlockThrowsBadStateExceptionOnNegativePosition(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "position" has an invalid state. Position cannot be negative.');

        $this->blockHandler->copyBlock(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(3, Value::STATUS_DRAFT),
            'root',
            -1
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlock
     */
    public function testCopyBlockThrowsBadStateExceptionOnTooLargePosition(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "position" has an invalid state. Position is out of range.');

        $this->blockHandler->copyBlock(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(3, Value::STATUS_DRAFT),
            'root',
            9999
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlockCollections
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCopyBlockWithChildBlocks(): void
    {
        $copiedBlock = $this->withUuids(
            function (): Block {
                return $this->blockHandler->copyBlock(
                    $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT),
                    $this->blockHandler->loadBlock(7, Value::STATUS_DRAFT),
                    'root'
                );
            },
            [
                'f06f245a-f951-52c8-bfa3-84c80154eadc',
                '4adf0f00-f6c2-5297-9f96-039bfabe8d3b',
                '805895b2-6292-5243-a0c0-06a6ec0e28a2',
            ]
        );

        self::assertSame(
            [
                'id' => 39,
                'uuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'layoutId' => 2,
                'layoutUuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                'depth' => 1,
                'path' => '/7/39/',
                'parentId' => 7,
                'parentUuid' => '8c4a5851-f2e0-5b46-a726-25230b5a3b9b',
                'placeholder' => 'root',
                'position' => 3,
                'definitionIdentifier' => 'two_columns',
                'parameters' => [
                    'en' => [],
                ],
                'config' => [],
                'viewType' => 'two_columns_50_50',
                'itemViewType' => 'standard',
                'name' => 'My third block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($copiedBlock)
        );

        $copiedSubBlock = $this->blockHandler->loadBlock(40, Value::STATUS_DRAFT);

        self::assertSame(
            [
                'id' => 40,
                'uuid' => '4adf0f00-f6c2-5297-9f96-039bfabe8d3b',
                'layoutId' => 2,
                'layoutUuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                'depth' => 2,
                'path' => '/7/39/40/',
                'parentId' => 39,
                'parentUuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'placeholder' => 'left',
                'position' => 0,
                'definitionIdentifier' => 'text',
                'parameters' => [
                    'en' => [
                        'content' => 'Text',
                    ],
                ],
                'config' => [],
                'viewType' => 'text',
                'itemViewType' => 'standard',
                'name' => 'My seventh block',
                'isTranslatable' => false,
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($copiedSubBlock)
        );

        self::assertSame(
            [
                [
                    'blockId' => 40,
                    'blockStatus' => Value::STATUS_DRAFT,
                    'collectionId' => 7,
                    'collectionStatus' => Value::STATUS_DRAFT,
                    'identifier' => 'default',
                ],
            ],
            $this->exportObjectList(
                $this->blockHandler->loadCollectionReferences($copiedSubBlock)
            )
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlockCollections
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlockTranslation
     */
    public function testCopyBlockToBlockInDifferentLayout(): void
    {
        $copiedBlock = $this->withUuids(
            function (): Block {
                return $this->blockHandler->copyBlock(
                    $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
                    $this->blockHandler->loadBlock(8, Value::STATUS_DRAFT),
                    'root'
                );
            },
            [
                'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'cf29cf92-0294-5581-abdb-58d11978186b',
                '805895b2-6292-5243-a0c0-06a6ec0e28a2',
                '76b05000-33ac-53f7-adfd-c91936d1f6b1',
                '6dc13cc7-fd76-5e41-8b0c-1ed93ece7fcf',
                '70fe4f3a-7e9d-5a1f-9e6a-b038c06ea117',
                '3a3aa59a-76fe-532f-8a03-c04a93d803f6',
                'f08717e5-5910-574d-b976-03d877c4729b',
                'e804ebd6-dc99-53bb-85d5-196d68933761',
                '910f4fe2-97b0-5599-8a45-8fb8a8e0ca6d',
            ]
        );

        self::assertSame(
            [
                'id' => 39,
                'uuid' => 'f06f245a-f951-52c8-bfa3-84c80154eadc',
                'layoutId' => 2,
                'layoutUuid' => '71cbe281-430c-51d5-8e21-c3cc4e656dac',
                'depth' => 1,
                'path' => '/8/39/',
                'parentId' => 8,
                'parentUuid' => '842b223f-3d9c-58a3-97fd-d610a4479224',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($copiedBlock)
        );

        self::assertSame(
            [
                [
                    'blockId' => 39,
                    'blockStatus' => Value::STATUS_DRAFT,
                    'collectionId' => 7,
                    'collectionStatus' => Value::STATUS_DRAFT,
                    'identifier' => 'default',
                ],
                [
                    'blockId' => 39,
                    'blockStatus' => Value::STATUS_DRAFT,
                    'collectionId' => 8,
                    'collectionStatus' => Value::STATUS_DRAFT,
                    'identifier' => 'featured',
                ],
            ],
            $this->exportObjectList($this->blockHandler->loadCollectionReferences($copiedBlock))
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlock
     */
    public function testCopyBlockBelowSelf(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "targetBlock" has an invalid state. Block cannot be copied below itself or its children.');

        $this->blockHandler->copyBlock(
            $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT),
            'main'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::copyBlock
     */
    public function testCopyBlockBelowChildren(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "targetBlock" has an invalid state. Block cannot be copied below itself or its children.');

        $this->blockHandler->copyBlock(
            $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(37, Value::STATUS_DRAFT),
            'main'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::moveBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::moveBlock
     */
    public function testMoveBlock(): void
    {
        $movedBlock = $this->blockHandler->moveBlock(
            $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(4, Value::STATUS_DRAFT),
            'root',
            0
        );

        self::assertSame(
            [
                'id' => 33,
                'uuid' => 'e666109d-f1db-5fd5-97fa-346f50e9ae59',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/4/33/',
                'parentId' => 4,
                'parentUuid' => 'eaffe0e7-2cce-58c4-b1ca-ae29f7de61d1',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'two_columns',
                'parameters' => [
                    'en' => [],
                ],
                'config' => [],
                'viewType' => 'two_columns_50_50',
                'itemViewType' => 'standard',
                'name' => 'My third block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($movedBlock)
        );

        self::assertSame(
            [
                'id' => 37,
                'uuid' => '129f51de-a535-5094-8517-45d672e06302',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 2,
                'path' => '/4/33/37/',
                'parentId' => 33,
                'parentUuid' => 'e666109d-f1db-5fd5-97fa-346f50e9ae59',
                'placeholder' => 'left',
                'position' => 0,
                'definitionIdentifier' => 'text',
                'parameters' => [
                    'en' => [
                        'content' => 'Text',
                    ],
                ],
                'config' => [],
                'viewType' => 'text',
                'itemViewType' => 'standard',
                'name' => 'My seventh block',
                'isTranslatable' => false,
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($this->blockHandler->loadBlock(37, Value::STATUS_DRAFT))
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::moveBlock
     */
    public function testMoveBlockThrowsBadStateExceptionOnMovingToSamePlace(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "targetBlock" has an invalid state. Block is already in specified target block and placeholder.');

        $this->blockHandler->moveBlock(
            $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(7, Value::STATUS_DRAFT),
            'root',
            0
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::moveBlock
     */
    public function testMoveBlockThrowsBadStateExceptionOnMovingToSelf(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "targetBlock" has an invalid state. Block cannot be moved below itself or its children.');

        $this->blockHandler->moveBlock(
            $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT),
            'main',
            0
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::moveBlock
     */
    public function testMoveBlockThrowsBadStateExceptionOnMovingToChildren(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "targetBlock" has an invalid state. Block cannot be moved below itself or its children.');

        $this->blockHandler->moveBlock(
            $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(37, Value::STATUS_DRAFT),
            'main',
            0
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::moveBlock
     */
    public function testMoveBlockThrowsBadStateExceptionOnNegativePosition(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "position" has an invalid state. Position cannot be negative.');

        $this->blockHandler->moveBlock(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(4, Value::STATUS_DRAFT),
            'root',
            -1
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::moveBlock
     */
    public function testMoveBlockThrowsBadStateExceptionOnTooLargePosition(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "position" has an invalid state. Position is out of range.');

        $this->blockHandler->moveBlock(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            $this->blockHandler->loadBlock(4, Value::STATUS_DRAFT),
            'root',
            9999
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::moveBlockToPosition
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::moveBlock
     */
    public function testMoveBlockToPosition(): void
    {
        $movedBlock = $this->blockHandler->moveBlockToPosition(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            1
        );

        self::assertSame(
            [
                'id' => 31,
                'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/31/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 1,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                    'hr' => [
                        'css_class' => 'css-class-hr',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($movedBlock)
        );

        $firstBlock = $this->blockHandler->loadBlock(32, Value::STATUS_DRAFT);
        self::assertSame(0, $firstBlock->position);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::moveBlockToPosition
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::moveBlock
     */
    public function testMoveBlockToLowerPosition(): void
    {
        $movedBlock = $this->blockHandler->moveBlockToPosition(
            $this->blockHandler->loadBlock(35, Value::STATUS_DRAFT),
            0
        );

        self::assertSame(
            [
                'id' => 35,
                'uuid' => 'c2a30ea3-95ef-55b0-a584-fbcfd93cec9e',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/35/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 3,
                    ],
                ],
                'config' => [],
                'viewType' => 'grid',
                'itemViewType' => 'standard',
                'name' => 'My fourth block',
                'isTranslatable' => false,
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($movedBlock)
        );

        $firstBlock = $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);
        self::assertSame(1, $firstBlock->position);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::moveBlockToPosition
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::moveBlock
     */
    public function testMoveBlockThrowsBadStateExceptionOnMovingRootBlock(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "position" has an invalid state. Root blocks cannot be moved.');

        $this->blockHandler->moveBlockToPosition(
            $this->blockHandler->loadBlock(1, Value::STATUS_DRAFT),
            1
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::moveBlockToPosition
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::moveBlock
     */
    public function testMoveBlockToPositionThrowsBadStateExceptionOnNegativePosition(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "position" has an invalid state. Position cannot be negative.');

        $this->blockHandler->moveBlockToPosition(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            -1
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::moveBlockToPosition
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::moveBlock
     */
    public function testMoveBlockToPositionThrowsBadStateExceptionOnTooLargePosition(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "position" has an invalid state. Position is out of range.');

        $this->blockHandler->moveBlockToPosition(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            9999
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlockCollectionsStatus
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::createBlockStatus
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::createBlock
     */
    public function testCreateBlockStatus(): void
    {
        $this->blockHandler->deleteBlock(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT)
        );

        $block = $this->blockHandler->createBlockStatus(
            $this->blockHandler->loadBlock(31, Value::STATUS_PUBLISHED),
            Value::STATUS_DRAFT
        );

        self::assertSame(
            [
                'id' => 31,
                'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/31/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 3,
                    ],
                    'hr' => [
                        'number_of_columns' => 3,
                    ],
                ],
                'config' => [],
                'viewType' => 'grid',
                'itemViewType' => 'standard_with_intro',
                'name' => 'My published block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($block)
        );

        $collectionReferences = $this->blockHandler->loadCollectionReferences($block);

        self::assertCount(2, $collectionReferences);

        $collectionIds = [
            $collectionReferences[0]->collectionId,
            $collectionReferences[1]->collectionId,
        ];

        self::assertContains(2, $collectionIds);
        self::assertContains(3, $collectionIds);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::restoreBlock
     */
    public function testRestoreBlock(): void
    {
        $block = $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);

        $movedBlock = $this->blockHandler->moveBlock(
            $block,
            $this->blockHandler->loadBlock(2, Value::STATUS_DRAFT),
            'root',
            1
        );

        $restoredBlock = $this->blockHandler->restoreBlock($movedBlock, Value::STATUS_PUBLISHED);

        self::assertSame(
            [
                'id' => 31,
                'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/2/31/',
                'parentId' => 2,
                'parentUuid' => '39d3ab66-1589-540f-95c4-6381acb4f010',
                'placeholder' => 'root',
                'position' => 1,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 3,
                    ],
                    'hr' => [
                        'number_of_columns' => 3,
                    ],
                ],
                'config' => [],
                'viewType' => 'grid',
                'itemViewType' => 'standard_with_intro',
                'name' => 'My published block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en', 'hr'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($restoredBlock)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::restoreBlock
     */
    public function testRestoreBlockThrowsBadStateExceptionWithSameState(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "block" has an invalid state. Block is already in provided status.');

        $block = $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);

        $this->blockHandler->restoreBlock($block, Value::STATUS_DRAFT);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::deleteBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::deleteBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadSubBlockIds
     */
    public function testDeleteBlock(): void
    {
        $this->blockHandler->deleteBlock(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT)
        );

        $secondBlock = $this->blockHandler->loadBlock(32, Value::STATUS_DRAFT);
        self::assertSame(0, $secondBlock->position);

        try {
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);
            self::fail('Block still exists after deleting');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        try {
            $this->collectionHandler->loadCollection(1, Value::STATUS_DRAFT);
            self::fail('Collection still exists after deleting a block.');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        // Verify that shared collection still exists
        $this->collectionHandler->loadCollection(3, Value::STATUS_PUBLISHED);
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::deleteBlock
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::getPositionHelperConditions
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::deleteBlocks
     * @doesNotPerformAssertions
     */
    public function testDeleteBlockWithSubBlocks(): void
    {
        $this->blockHandler->deleteBlock(
            $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT)
        );

        try {
            $this->blockHandler->loadBlock(33, Value::STATUS_DRAFT);
            self::fail('Block still exists after deleting');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        try {
            $this->blockHandler->loadBlock(37, Value::STATUS_DRAFT);
            self::fail('Sub-block still exists after deleting');
        } catch (NotFoundException $e) {
            // Do nothing
        }

        try {
            $this->collectionHandler->loadCollection(6, Value::STATUS_DRAFT);
            self::fail('Collection still exists after deleting a sub-block.');
        } catch (NotFoundException $e) {
            // Do nothing
        }
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::deleteBlockTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::deleteBlockTranslations
     */
    public function testDeleteBlockTranslation(): void
    {
        $block = $this->blockHandler->deleteBlockTranslation(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'hr'
        );

        self::assertSame(
            [
                'id' => 31,
                'uuid' => '28df256a-2467-5527-b398-9269ccc652de',
                'layoutId' => 1,
                'layoutUuid' => '81168ed3-86f9-55ea-b153-101f96f2c136',
                'depth' => 1,
                'path' => '/3/31/',
                'parentId' => 3,
                'parentUuid' => '96c7f078-a430-5a82-8d19-107182fb463f',
                'placeholder' => 'root',
                'position' => 0,
                'definitionIdentifier' => 'list',
                'parameters' => [
                    'en' => [
                        'number_of_columns' => 2,
                        'css_class' => 'css-class',
                        'css_id' => 'css-id',
                    ],
                ],
                'config' => [],
                'viewType' => 'list',
                'itemViewType' => 'standard',
                'name' => 'My block',
                'isTranslatable' => true,
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'alwaysAvailable' => true,
                'status' => Value::STATUS_DRAFT,
            ],
            $this->exportObject($block)
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::deleteBlockTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::deleteBlockTranslations
     */
    public function testDeleteBlockTranslationWithNonExistingLocale(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "locale" has an invalid state. Block does not have the provided locale.');

        $this->blockHandler->deleteBlockTranslation(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'de'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::deleteBlockTranslation
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::deleteBlockTranslations
     */
    public function testDeleteBlockTranslationWithMainLocale(): void
    {
        $this->expectException(BadStateException::class);
        $this->expectExceptionMessage('Argument "locale" has an invalid state. Main translation cannot be removed from the block.');

        $this->blockHandler->deleteBlockTranslation(
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT),
            'en'
        );
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::deleteLayoutBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::deleteBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::deleteCollectionReferences
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadBlockCollectionIds
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadLayoutBlockIds
     */
    public function testDeleteLayoutBlocks(): void
    {
        $layout = $this->layoutHandler->loadLayout(1, Value::STATUS_DRAFT);

        // First we need to delete all zones to correctly delete the blocks
        $query = $this->databaseConnection->createQueryBuilder();

        $query->delete('nglayouts_zone')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('layout_id', ':layout_id'),
                    $query->expr()->eq('status', ':status')
                )
            )
            ->setParameter('layout_id', $layout->id, Type::INTEGER)
            ->setParameter('status', $layout->status, Type::INTEGER);

        $query->execute();

        $this->blockHandler->deleteLayoutBlocks($layout->id, $layout->status);

        self::assertEmpty($this->blockHandler->loadLayoutBlocks($layout));
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::deleteBlockCollections
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::deleteBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::deleteBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::deleteCollectionReferences
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadBlockCollectionIds
     * @doesNotPerformAssertions
     */
    public function testDeleteBlocks(): void
    {
        $this->blockHandler->deleteBlocks([31, 32]);

        try {
            $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);
            self::fail(
                sprintf(
                    'Draft block %d still available after deleting',
                    31
                )
            );
        } catch (NotFoundException $e) {
            // Do nothing
        }

        try {
            $this->blockHandler->loadBlock(32, Value::STATUS_DRAFT);
            self::fail(
                sprintf(
                    'Draft block %d still available after deleting',
                    32
                )
            );
        } catch (NotFoundException $e) {
            // Do nothing
        }

        try {
            $this->blockHandler->loadBlock(31, Value::STATUS_PUBLISHED);
            self::fail(
                sprintf(
                    'Published block %d still available after deleting',
                    31
                )
            );
        } catch (NotFoundException $e) {
            // Do nothing
        }

        try {
            $this->blockHandler->loadBlock(32, Value::STATUS_PUBLISHED);
            self::fail(
                sprintf(
                    'Published block %d still available after deleting',
                    32
                )
            );
        } catch (NotFoundException $e) {
            // Do nothing
        }
    }

    /**
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::deleteBlockCollections
     * @covers \Netgen\Layouts\Persistence\Doctrine\Handler\BlockHandler::deleteBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::deleteBlocks
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::deleteCollectionReferences
     * @covers \Netgen\Layouts\Persistence\Doctrine\QueryHandler\BlockQueryHandler::loadBlockCollectionIds
     */
    public function testDeleteBlocksInStatus(): void
    {
        $this->blockHandler->deleteBlocks([31, 32], Value::STATUS_PUBLISHED);

        $this->blockHandler->loadBlock(31, Value::STATUS_DRAFT);
        $this->blockHandler->loadBlock(32, Value::STATUS_DRAFT);

        try {
            $this->blockHandler->loadBlock(31, Value::STATUS_PUBLISHED);
            self::fail(
                sprintf(
                    'Published block %d still available after deleting',
                    31
                )
            );
        } catch (NotFoundException $e) {
            // Do nothing
        }

        try {
            $this->blockHandler->loadBlock(32, Value::STATUS_PUBLISHED);
            self::fail(
                sprintf(
                    'Published block %d still available after deleting',
                    32
                )
            );
        } catch (NotFoundException $e) {
            // Do nothing
        }

        // We fake the assertion count to disable risky warning
        $this->addToAssertionCount(1);
    }
}
