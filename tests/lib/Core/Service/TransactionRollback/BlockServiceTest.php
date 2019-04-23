<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Core\Service\TransactionRollback;

use Exception;
use Netgen\Layouts\API\Service\LayoutService;
use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\API\Values\Block\BlockCreateStruct;
use Netgen\Layouts\API\Values\Block\BlockUpdateStruct;
use Netgen\Layouts\API\Values\Layout\Zone;
use Netgen\Layouts\API\Values\Value;
use Netgen\Layouts\Block\BlockDefinition;
use Netgen\Layouts\Block\ContainerDefinition;
use Netgen\Layouts\Persistence\Values\Block\Block as PersistenceBlock;
use Netgen\Layouts\Persistence\Values\Layout\Layout as PersistenceLayout;
use Netgen\Layouts\Persistence\Values\Layout\Zone as PersistenceZone;
use Netgen\Layouts\Tests\Block\Stubs\ContainerDefinitionHandler;
use Ramsey\Uuid\Uuid;

/**
 * @property \PHPUnit\Framework\MockObject\MockObject $layoutService
 */
final class BlockServiceTest extends TestCase
{
    /**
     * @covers \Netgen\Layouts\Core\Service\BlockService::createBlock
     * @covers \Netgen\Layouts\Core\Service\BlockService::internalCreateBlock
     */
    public function testCreateBlock(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutHandler
            ->expects(self::at(0))
            ->method('loadLayout')
            ->willReturn(PersistenceLayout::fromArray(['availableLocales' => ['en']]));

        $this->blockHandler
            ->expects(self::at(0))
            ->method('loadBlock')
            ->willReturn(new PersistenceBlock());

        $this->blockHandler
            ->expects(self::at(1))
            ->method('createBlock')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->blockService->createBlock(
            new BlockCreateStruct(BlockDefinition::fromArray(['identifier' => 'definition'])),
            Block::fromArray(
                [
                    'status' => Value::STATUS_DRAFT,
                    'definition' => ContainerDefinition::fromArray(
                        [
                            'handler' => new ContainerDefinitionHandler([], ['main']),
                        ]
                    ),
                ]
            ),
            'main'
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\BlockService::createBlockInZone
     * @covers \Netgen\Layouts\Core\Service\BlockService::internalCreateBlock
     */
    public function testCreateBlockInZone(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutHandler
            ->expects(self::at(0))
            ->method('loadLayout')
            ->willReturn(PersistenceLayout::fromArray(['type' => '4_zones_a']));

        $this->layoutHandler
            ->expects(self::at(1))
            ->method('loadZone')
            ->willReturn(PersistenceZone::fromArray(['status' => Value::STATUS_DRAFT, 'identifier' => 'left']));

        $this->blockHandler
            ->expects(self::at(0))
            ->method('loadBlock')
            ->willReturn(new PersistenceBlock());

        $this->blockHandler
            ->expects(self::at(1))
            ->method('createBlock')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->blockService->createBlockInZone(
            new BlockCreateStruct(BlockDefinition::fromArray(['identifier' => 'definition'])),
            Zone::fromArray(['layoutId' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT, 'identifier' => 'left'])
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\BlockService::updateBlock
     */
    public function testUpdateBlock(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $persistenceBlock = PersistenceBlock::fromArray(
            [
                'config' => [],
                'mainLocale' => 'en',
                'availableLocales' => ['en'],
                'parameters' => ['en' => []],
            ]
        );

        $this->blockHandler
            ->expects(self::at(0))
            ->method('loadBlock')
            ->willReturn($persistenceBlock);

        $this->blockHandler
            ->expects(self::at(1))
            ->method('updateBlockTranslation')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $struct = new BlockUpdateStruct();
        $struct->locale = 'en';

        $this->blockService->updateBlock(
            Block::fromArray(
                [
                    'status' => Value::STATUS_DRAFT,
                    'definition' => new BlockDefinition(),
                    'configs' => [],
                ]
            ),
            $struct
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\BlockService::copyBlock
     */
    public function testCopyBlock(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->blockHandler
            ->expects(self::at(0))
            ->method('loadBlock')
            ->willReturn(new PersistenceBlock());

        $this->blockHandler
            ->expects(self::at(1))
            ->method('loadBlock')
            ->willReturn(new PersistenceBlock());

        $this->blockHandler
            ->expects(self::at(2))
            ->method('copyBlock')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->blockService->copyBlock(
            Block::fromArray(['status' => Value::STATUS_DRAFT, 'definition' => new BlockDefinition()]),
            Block::fromArray(
                [
                    'status' => Value::STATUS_DRAFT,
                    'definition' => ContainerDefinition::fromArray(
                        [
                            'handler' => new ContainerDefinitionHandler([], ['main']),
                        ]
                    ),
                ]
            ),
            'main'
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\BlockService::copyBlockToZone
     */
    public function testCopyBlockToZone(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->blockHandler
            ->expects(self::at(0))
            ->method('loadBlock')
            ->willReturn(new PersistenceBlock());

        $this->layoutHandler
            ->expects(self::at(0))
            ->method('loadLayout')
            ->willReturn(PersistenceLayout::fromArray(['type' => '4_zones_a']));

        $this->layoutHandler
            ->expects(self::at(1))
            ->method('loadZone')
            ->willReturn(PersistenceZone::fromArray(['status' => Value::STATUS_DRAFT, 'identifier' => 'left']));

        $this->blockHandler
            ->expects(self::at(1))
            ->method('loadBlock')
            ->willReturn(new PersistenceBlock());

        $this->blockHandler
            ->expects(self::at(2))
            ->method('copyBlock')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->blockService->copyBlockToZone(
            Block::fromArray(['status' => Value::STATUS_DRAFT, 'definition' => new BlockDefinition()]),
            Zone::fromArray(['layoutId' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT, 'identifier' => 'left'])
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\BlockService::internalMoveBlock
     * @covers \Netgen\Layouts\Core\Service\BlockService::moveBlockToZone
     */
    public function testMoveBlock(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->blockHandler
            ->expects(self::at(0))
            ->method('loadBlock')
            ->willReturn(PersistenceBlock::fromArray(['parentId' => 1, 'placeholder' => 'main']));

        $this->blockHandler
            ->expects(self::at(1))
            ->method('loadBlock')
            ->willReturn(PersistenceBlock::fromArray(['id' => 1]));

        $this->blockHandler
            ->expects(self::at(2))
            ->method('moveBlockToPosition')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->blockService->moveBlock(
            Block::fromArray(['status' => Value::STATUS_DRAFT, 'definition' => new BlockDefinition()]),
            Block::fromArray(
                [
                    'status' => Value::STATUS_DRAFT,
                    'definition' => ContainerDefinition::fromArray(
                        [
                            'handler' => new ContainerDefinitionHandler([], ['main']),
                        ]
                    ),
                ]
            ),
            'main',
            0
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\BlockService::internalMoveBlock
     * @covers \Netgen\Layouts\Core\Service\BlockService::moveBlockToZone
     */
    public function testMoveBlockToZone(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->blockHandler
            ->expects(self::at(0))
            ->method('loadBlock')
            ->willReturn(PersistenceBlock::fromArray(['parentId' => 1, 'placeholder' => 'root']));

        $this->layoutHandler
            ->expects(self::at(0))
            ->method('loadLayout')
            ->willReturn(PersistenceLayout::fromArray(['type' => '4_zones_a']));

        $this->layoutHandler
            ->expects(self::at(1))
            ->method('loadZone')
            ->willReturn(PersistenceZone::fromArray(['status' => Value::STATUS_DRAFT, 'identifier' => 'left']));

        $this->blockHandler
            ->expects(self::at(1))
            ->method('loadBlock')
            ->willReturn(PersistenceBlock::fromArray(['id' => 1]));

        $this->blockHandler
            ->expects(self::at(2))
            ->method('moveBlockToPosition')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->blockService->moveBlockToZone(
            Block::fromArray(['status' => Value::STATUS_DRAFT, 'definition' => new BlockDefinition()]),
            Zone::fromArray(['layoutId' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT, 'identifier' => 'left']),
            0
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\BlockService::restoreBlock
     */
    public function testRestoreBlock(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->blockHandler
            ->expects(self::at(0))
            ->method('loadBlock')
            ->willReturn(new PersistenceBlock());

        $this->layoutHandler
            ->expects(self::at(0))
            ->method('loadLayout')
            ->willReturn(new PersistenceLayout());

        $this->blockHandler
            ->expects(self::at(1))
            ->method('restoreBlock')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->blockService->restoreBlock(Block::fromArray(['status' => Value::STATUS_DRAFT]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\BlockService::enableTranslations
     */
    public function testEnableTranslations(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->blockHandler
            ->expects(self::at(0))
            ->method('loadBlock')
            ->willReturn(PersistenceBlock::fromArray(['isTranslatable' => false, 'parentId' => 42]));

        $this->blockHandler
            ->expects(self::at(1))
            ->method('loadBlock')
            ->willReturn(PersistenceBlock::fromArray(['isTranslatable' => true, 'depth' => 1]));

        $this->layoutHandler
            ->expects(self::at(0))
            ->method('loadLayout')
            ->willReturn(new PersistenceLayout());

        $this->blockHandler
            ->expects(self::at(2))
            ->method('updateBlock')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->blockService->enableTranslations(Block::fromArray(['status' => Value::STATUS_DRAFT]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\BlockService::disableTranslations
     */
    public function testDisableTranslations(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->blockHandler
            ->expects(self::at(0))
            ->method('loadBlock')
            ->willReturn(PersistenceBlock::fromArray(['isTranslatable' => true]));

        $this->blockHandler
            ->expects(self::at(1))
            ->method('updateBlock')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->blockService->disableTranslations(Block::fromArray(['status' => Value::STATUS_DRAFT]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\BlockService::deleteBlock
     */
    public function testDeleteBlock(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->blockHandler
            ->expects(self::at(0))
            ->method('loadBlock')
            ->willReturn(new PersistenceBlock());

        $this->blockHandler
            ->expects(self::at(1))
            ->method('deleteBlock')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->blockService->deleteBlock(Block::fromArray(['status' => Value::STATUS_DRAFT]));
    }

    protected function createLayoutService(): LayoutService
    {
        return $this->createMock(LayoutService::class);
    }
}
