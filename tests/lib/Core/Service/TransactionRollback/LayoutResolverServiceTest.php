<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Core\Service\TransactionRollback;

use Exception;
use Netgen\Layouts\API\Values\LayoutResolver\ConditionCreateStruct;
use Netgen\Layouts\API\Values\LayoutResolver\ConditionUpdateStruct;
use Netgen\Layouts\API\Values\LayoutResolver\Rule;
use Netgen\Layouts\API\Values\LayoutResolver\RuleCondition;
use Netgen\Layouts\API\Values\LayoutResolver\RuleCreateStruct;
use Netgen\Layouts\API\Values\LayoutResolver\RuleGroup;
use Netgen\Layouts\API\Values\LayoutResolver\RuleMetadataUpdateStruct;
use Netgen\Layouts\API\Values\LayoutResolver\RuleUpdateStruct;
use Netgen\Layouts\API\Values\LayoutResolver\Target;
use Netgen\Layouts\API\Values\LayoutResolver\TargetCreateStruct;
use Netgen\Layouts\API\Values\LayoutResolver\TargetUpdateStruct;
use Netgen\Layouts\API\Values\Value;
use Netgen\Layouts\Layout\Resolver\ConditionType\RouteParameter;
use Netgen\Layouts\Layout\Resolver\TargetType\Route;
use Netgen\Layouts\Persistence\Values\LayoutResolver\Rule as PersistenceRule;
use Netgen\Layouts\Persistence\Values\LayoutResolver\RuleCondition as PersistenceRuleCondition;
use Netgen\Layouts\Persistence\Values\LayoutResolver\RuleGroup as PersistenceRuleGroup;
use Netgen\Layouts\Persistence\Values\LayoutResolver\Target as PersistenceTarget;
use Ramsey\Uuid\Uuid;

final class LayoutResolverServiceTest extends TestCase
{
    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::createRule
     */
    public function testCreateRule(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRuleGroup')
            ->willReturn(new PersistenceRuleGroup());

        $this->layoutResolverHandler
            ->method('createRule')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->createRule(
            new RuleCreateStruct(),
            RuleGroup::fromArray(
                [
                    'id' => Uuid::uuid4(),
                    'status' => Value::STATUS_PUBLISHED,
                ]
            )
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::updateRule
     */
    public function testUpdateRule(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(PersistenceRule::fromArray(['id' => 42]));

        $this->layoutResolverHandler
            ->method('updateRule')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->updateRule(
            Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT]),
            new RuleUpdateStruct()
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::updateRuleMetadata
     */
    public function testUpdateRuleMetadata(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(PersistenceRule::fromArray(['id' => 42]));

        $this->layoutResolverHandler
            ->method('updateRuleMetadata')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->updateRuleMetadata(
            Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_PUBLISHED]),
            new RuleMetadataUpdateStruct()
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::copyRule
     */
    public function testCopyRule(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(PersistenceRule::fromArray(['id' => 42]));

        $this->layoutResolverHandler
            ->method('copyRule')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->copyRule(Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Rule::STATUS_PUBLISHED]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::createDraft
     */
    public function testCreateDraft(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(PersistenceRule::fromArray(['id' => 42]));

        $this->layoutResolverHandler
            ->method('ruleExists')
            ->willReturn(false);

        $this->layoutResolverHandler
            ->method('deleteRule')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->createDraft(Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_PUBLISHED]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::discardDraft
     */
    public function testDiscardDraft(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(PersistenceRule::fromArray(['id' => 42]));

        $this->layoutResolverHandler
            ->method('deleteRule')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->discardDraft(Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::publishRule
     */
    public function testPublishRule(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(PersistenceRule::fromArray(['id' => 42]));

        $this->layoutResolverHandler
            ->method('deleteRule')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->publishRule(Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::restoreFromArchive
     */
    public function testRestoreFromArchive(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(PersistenceRule::fromArray(['id' => 42]));

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(PersistenceRule::fromArray(['id' => 42]));

        $this->layoutResolverHandler
            ->method('deleteRule')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->restoreFromArchive(Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Rule::STATUS_ARCHIVED]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::deleteRule
     */
    public function testDeleteRule(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(PersistenceRule::fromArray(['id' => 42]));

        $this->layoutResolverHandler
            ->method('deleteRule')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->deleteRule(Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Rule::STATUS_DRAFT]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::enableRule
     */
    public function testEnableRule(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(
                PersistenceRule::fromArray(
                    [
                        'layoutUuid' => Uuid::uuid4()->toString(),
                        'enabled' => false,
                    ]
                )
            );

        $this->layoutResolverHandler
            ->method('updateRuleMetadata')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->enableRule(Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_PUBLISHED]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::disableRule
     */
    public function testDisableRule(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(
                PersistenceRule::fromArray(
                    [
                        'enabled' => true,
                    ]
                )
            );

        $this->layoutResolverHandler
            ->method('updateRuleMetadata')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->disableRule(Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_PUBLISHED]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::addTarget
     */
    public function testAddTarget(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(PersistenceRule::fromArray(['id' => 42]));

        $this->layoutResolverHandler
            ->method('loadRuleTargets')
            ->willReturn([]);

        $this->layoutResolverHandler
            ->method('addTarget')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $targetCreateStruct = new TargetCreateStruct();
        $targetCreateStruct->type = 'route';

        $this->layoutResolverService->addTarget(
            Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT]),
            $targetCreateStruct
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::updateTarget
     */
    public function testUpdateTarget(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadTarget')
            ->willReturn(new PersistenceTarget());

        $this->layoutResolverHandler
            ->method('updateTarget')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->updateTarget(
            Target::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT, 'targetType' => new Route()]),
            new TargetUpdateStruct()
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::deleteTarget
     */
    public function testDeleteTarget(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadTarget')
            ->willReturn(new PersistenceTarget());

        $this->layoutResolverHandler
            ->method('deleteTarget')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->deleteTarget(Target::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT]));
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::addCondition
     */
    public function testAddCondition(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRule')
            ->willReturn(PersistenceRule::fromArray(['id' => 42]));

        $this->layoutResolverHandler
            ->method('addRuleCondition')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $conditionCreateStruct = new ConditionCreateStruct();
        $conditionCreateStruct->type = 'route_parameter';

        $this->layoutResolverService->addCondition(
            Rule::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT]),
            $conditionCreateStruct
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::updateCondition
     */
    public function testUpdateCondition(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRuleCondition')
            ->willReturn(new PersistenceRuleCondition());

        $this->layoutResolverHandler
            ->method('updateCondition')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->updateCondition(
            RuleCondition::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT, 'conditionType' => new RouteParameter()]),
            new ConditionUpdateStruct()
        );
    }

    /**
     * @covers \Netgen\Layouts\Core\Service\LayoutResolverService::deleteCondition
     */
    public function testDeleteCondition(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test exception text');

        $this->layoutResolverHandler
            ->method('loadRuleCondition')
            ->willReturn(new PersistenceRuleCondition());

        $this->layoutResolverHandler
            ->method('deleteCondition')
            ->willThrowException(new Exception('Test exception text'));

        $this->transactionHandler
            ->expects(self::once())
            ->method('rollbackTransaction');

        $this->layoutResolverService->deleteCondition(RuleCondition::fromArray(['id' => Uuid::uuid4(), 'status' => Value::STATUS_DRAFT]));
    }
}
