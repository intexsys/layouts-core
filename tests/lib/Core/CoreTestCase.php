<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Core;

use Netgen\Layouts\API\Service\BlockService as APIBlockService;
use Netgen\Layouts\API\Service\CollectionService as APICollectionService;
use Netgen\Layouts\API\Service\LayoutResolverService as APILayoutResolverService;
use Netgen\Layouts\API\Service\LayoutService as APILayoutService;
use Netgen\Layouts\API\Values\Collection\Collection;
use Netgen\Layouts\Block\BlockDefinition;
use Netgen\Layouts\Block\BlockDefinition\Configuration\ItemViewType;
use Netgen\Layouts\Block\BlockDefinition\Configuration\ViewType;
use Netgen\Layouts\Block\ContainerDefinition;
use Netgen\Layouts\Block\Registry\BlockDefinitionRegistry;
use Netgen\Layouts\Block\Registry\BlockDefinitionRegistryInterface;
use Netgen\Layouts\Collection\Item\ItemDefinition;
use Netgen\Layouts\Collection\Registry\ItemDefinitionRegistry;
use Netgen\Layouts\Collection\Registry\ItemDefinitionRegistryInterface;
use Netgen\Layouts\Collection\Registry\QueryTypeRegistry;
use Netgen\Layouts\Collection\Registry\QueryTypeRegistryInterface;
use Netgen\Layouts\Config\ConfigDefinition;
use Netgen\Layouts\Core\Mapper\BlockMapper;
use Netgen\Layouts\Core\Mapper\CollectionMapper;
use Netgen\Layouts\Core\Mapper\ConfigMapper;
use Netgen\Layouts\Core\Mapper\LayoutMapper;
use Netgen\Layouts\Core\Mapper\LayoutResolverMapper;
use Netgen\Layouts\Core\Mapper\ParameterMapper;
use Netgen\Layouts\Core\Service\BlockService;
use Netgen\Layouts\Core\Service\CollectionService;
use Netgen\Layouts\Core\Service\LayoutResolverService;
use Netgen\Layouts\Core\Service\LayoutService;
use Netgen\Layouts\Core\StructBuilder\BlockStructBuilder;
use Netgen\Layouts\Core\StructBuilder\CollectionStructBuilder;
use Netgen\Layouts\Core\StructBuilder\ConfigStructBuilder;
use Netgen\Layouts\Core\StructBuilder\LayoutResolverStructBuilder;
use Netgen\Layouts\Core\StructBuilder\LayoutStructBuilder;
use Netgen\Layouts\Core\Validator\BlockValidator;
use Netgen\Layouts\Core\Validator\CollectionValidator;
use Netgen\Layouts\Core\Validator\LayoutResolverValidator;
use Netgen\Layouts\Core\Validator\LayoutValidator;
use Netgen\Layouts\Item\CmsItemLoaderInterface;
use Netgen\Layouts\Item\Registry\ValueTypeRegistry;
use Netgen\Layouts\Layout\Registry\LayoutTypeRegistry;
use Netgen\Layouts\Layout\Registry\LayoutTypeRegistryInterface;
use Netgen\Layouts\Layout\Resolver\ConditionType;
use Netgen\Layouts\Layout\Resolver\Registry\ConditionTypeRegistry;
use Netgen\Layouts\Layout\Resolver\Registry\ConditionTypeRegistryInterface;
use Netgen\Layouts\Layout\Resolver\Registry\TargetTypeRegistry;
use Netgen\Layouts\Layout\Resolver\Registry\TargetTypeRegistryInterface;
use Netgen\Layouts\Layout\Resolver\TargetType;
use Netgen\Layouts\Layout\Type\LayoutType;
use Netgen\Layouts\Layout\Type\Zone;
use Netgen\Layouts\Parameters\ParameterType;
use Netgen\Layouts\Parameters\ParameterType\ItemLink\RemoteIdConverter;
use Netgen\Layouts\Parameters\Registry\ParameterTypeRegistry;
use Netgen\Layouts\Parameters\Registry\ParameterTypeRegistryInterface;
use Netgen\Layouts\Persistence\Handler\BlockHandlerInterface;
use Netgen\Layouts\Persistence\Handler\CollectionHandlerInterface;
use Netgen\Layouts\Persistence\Handler\LayoutHandlerInterface;
use Netgen\Layouts\Persistence\Handler\LayoutResolverHandlerInterface;
use Netgen\Layouts\Persistence\TransactionHandlerInterface;
use Netgen\Layouts\Tests\Block\Stubs\BlockDefinitionHandler;
use Netgen\Layouts\Tests\Block\Stubs\BlockDefinitionHandlerWithTranslatableParameter;
use Netgen\Layouts\Tests\Block\Stubs\ContainerDefinitionHandler;
use Netgen\Layouts\Tests\Collection\Stubs\QueryType;
use Netgen\Layouts\Tests\Config\Stubs\Block\ConfigHandler as BlockConfigHandler;
use Netgen\Layouts\Tests\Config\Stubs\CollectionItem\ConfigHandler as ItemConfigHandler;
use Netgen\Layouts\Tests\Layout\Resolver\Stubs\ConditionType1;
use Netgen\Layouts\Tests\Layout\Resolver\Stubs\TargetType1;
use Netgen\Layouts\Utils\HtmlPurifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class CoreTestCase extends TestCase
{
    /**
     * @var \Netgen\Layouts\Item\CmsItemLoaderInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cmsItemLoaderMock;

    /**
     * @var \Netgen\Layouts\Layout\Registry\LayoutTypeRegistryInterface
     */
    protected $layoutTypeRegistry;

    /**
     * @var \Netgen\Layouts\Collection\Registry\ItemDefinitionRegistryInterface
     */
    protected $itemDefinitionRegistry;

    /**
     * @var \Netgen\Layouts\Collection\Registry\QueryTypeRegistryInterface
     */
    protected $queryTypeRegistry;

    /**
     * @var \Netgen\Layouts\Block\Registry\BlockDefinitionRegistryInterface
     */
    protected $blockDefinitionRegistry;

    /**
     * @var \Netgen\Layouts\Layout\Resolver\Registry\TargetTypeRegistryInterface
     */
    protected $targetTypeRegistry;

    /**
     * @var \Netgen\Layouts\Layout\Resolver\Registry\ConditionTypeRegistryInterface
     */
    protected $conditionTypeRegistry;

    /**
     * @var \Netgen\Layouts\Parameters\Registry\ParameterTypeRegistryInterface
     */
    protected $parameterTypeRegistry;

    /**
     * @var \Netgen\Layouts\Persistence\TransactionHandlerInterface
     */
    protected $transactionHandler;

    /**
     * @var \Netgen\Layouts\Persistence\Handler\BlockHandlerInterface
     */
    protected $blockHandler;

    /**
     * @var \Netgen\Layouts\Persistence\Handler\LayoutHandlerInterface
     */
    protected $layoutHandler;

    /**
     * @var \Netgen\Layouts\Persistence\Handler\CollectionHandlerInterface
     */
    protected $collectionHandler;

    /**
     * @var \Netgen\Layouts\Persistence\Handler\LayoutResolverHandlerInterface
     */
    protected $layoutResolverHandler;

    /**
     * @var \Netgen\Layouts\API\Service\BlockService
     */
    protected $blockService;

    /**
     * @var \Netgen\Layouts\API\Service\LayoutService
     */
    protected $layoutService;

    /**
     * @var \Netgen\Layouts\API\Service\CollectionService
     */
    protected $collectionService;

    /**
     * @var \Netgen\Layouts\API\Service\LayoutResolverService
     */
    protected $layoutResolverService;

    public function setUp(): void
    {
        $this->transactionHandler = $this->createTransactionHandler();
        $this->layoutHandler = $this->createLayoutHandler();
        $this->blockHandler = $this->createBlockHandler();
        $this->collectionHandler = $this->createCollectionHandler();
        $this->layoutResolverHandler = $this->createLayoutResolverHandler();

        $this->cmsItemLoaderMock = $this->createMock(CmsItemLoaderInterface::class);

        $this->parameterTypeRegistry = $this->parameterTypeRegistry ?? $this->createParameterTypeRegistry();
        $this->layoutTypeRegistry = $this->layoutTypeRegistry ?? $this->createLayoutTypeRegistry();
        $this->itemDefinitionRegistry = $this->itemDefinitionRegistry ?? $this->createItemDefinitionRegistry();
        $this->queryTypeRegistry = $this->queryTypeRegistry ?? $this->createQueryTypeRegistry();
        $this->blockDefinitionRegistry = $this->blockDefinitionRegistry ?? $this->createBlockDefinitionRegistry();
        $this->targetTypeRegistry = $this->targetTypeRegistry ?? $this->createTargetTypeRegistry();
        $this->conditionTypeRegistry = $this->conditionTypeRegistry ?? $this->createConditionTypeRegistry();

        $this->layoutService = $this->layoutService ?? $this->createLayoutService();
        $this->blockService = $this->blockService ?? $this->createBlockService();
        $this->collectionService = $this->collectionService ?? $this->createCollectionService();
        $this->layoutResolverService = $this->layoutResolverService ?? $this->createLayoutResolverService();
    }

    abstract protected function createTransactionHandler(): TransactionHandlerInterface;

    abstract protected function createLayoutHandler(): LayoutHandlerInterface;

    abstract protected function createBlockHandler(): BlockHandlerInterface;

    abstract protected function createCollectionHandler(): CollectionHandlerInterface;

    abstract protected function createLayoutResolverHandler(): LayoutResolverHandlerInterface;

    protected function createValidator(): ValidatorInterface
    {
        $validator = $this->createMock(ValidatorInterface::class);

        $validator->expects(self::any())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        return $validator;
    }

    /**
     * Creates a layout service under test.
     */
    protected function createLayoutService(): APILayoutService
    {
        $layoutValidator = new LayoutValidator();
        $layoutValidator->setValidator($this->createValidator());

        return new LayoutService(
            $this->transactionHandler,
            $layoutValidator,
            $this->createLayoutMapper(),
            new LayoutStructBuilder(),
            $this->layoutHandler
        );
    }

    /**
     * Creates a block service under test.
     */
    protected function createBlockService(): APIBlockService
    {
        $validator = $this->createValidator();

        $collectionValidator = new CollectionValidator();
        $collectionValidator->setValidator($validator);

        $blockValidator = new BlockValidator($collectionValidator);
        $blockValidator->setValidator($validator);

        return new BlockService(
            $this->transactionHandler,
            $blockValidator,
            $this->createBlockMapper(),
            new BlockStructBuilder(
                new ConfigStructBuilder()
            ),
            $this->createParameterMapper(),
            $this->createConfigMapper(),
            $this->layoutTypeRegistry,
            $this->blockHandler,
            $this->layoutHandler,
            $this->collectionHandler
        );
    }

    /**
     * Creates a collection service under test.
     */
    protected function createCollectionService(): APICollectionService
    {
        $collectionValidator = new CollectionValidator();
        $collectionValidator->setValidator($this->createValidator());

        return new CollectionService(
            $this->transactionHandler,
            $collectionValidator,
            $this->createCollectionMapper(),
            new CollectionStructBuilder(
                new ConfigStructBuilder()
            ),
            $this->createParameterMapper(),
            $this->createConfigMapper(),
            $this->collectionHandler
        );
    }

    /**
     * Creates a layout resolver service under test.
     */
    protected function createLayoutResolverService(): APILayoutResolverService
    {
        $layoutResolverValidator = new LayoutResolverValidator(
            $this->targetTypeRegistry,
            $this->conditionTypeRegistry
        );

        $layoutResolverValidator->setValidator($this->createValidator());

        return new LayoutResolverService(
            $this->transactionHandler,
            $layoutResolverValidator,
            $this->createLayoutResolverMapper(),
            new LayoutResolverStructBuilder(),
            $this->layoutResolverHandler,
            $this->layoutHandler
        );
    }

    /**
     * Creates a layout mapper under test.
     */
    protected function createLayoutMapper(): LayoutMapper
    {
        return new LayoutMapper(
            $this->layoutHandler,
            $this->layoutTypeRegistry
        );
    }

    /**
     * Creates a block mapper under test.
     */
    protected function createBlockMapper(): BlockMapper
    {
        return new BlockMapper(
            $this->blockHandler,
            $this->collectionHandler,
            $this->createCollectionMapper(),
            $this->createParameterMapper(),
            $this->createConfigMapper(),
            $this->blockDefinitionRegistry
        );
    }

    /**
     * Creates a collection mapper under test.
     */
    protected function createCollectionMapper(): CollectionMapper
    {
        return new CollectionMapper(
            $this->collectionHandler,
            $this->createParameterMapper(),
            $this->createConfigMapper(),
            $this->itemDefinitionRegistry,
            $this->queryTypeRegistry,
            $this->cmsItemLoaderMock
        );
    }

    /**
     * Creates a layout resolver mapper under test.
     */
    protected function createLayoutResolverMapper(): LayoutResolverMapper
    {
        return new LayoutResolverMapper(
            $this->layoutResolverHandler,
            $this->targetTypeRegistry,
            $this->conditionTypeRegistry,
            $this->layoutService
        );
    }

    /**
     * Creates the parameter mapper under test.
     */
    protected function createParameterMapper(): ParameterMapper
    {
        return new ParameterMapper();
    }

    /**
     * Creates the config mapper under test.
     */
    protected function createConfigMapper(): ConfigMapper
    {
        return new ConfigMapper($this->createParameterMapper());
    }

    protected function createLayoutTypeRegistry(): LayoutTypeRegistryInterface
    {
        $layoutType1 = LayoutType::fromArray(
            [
                'identifier' => '4_zones_a',
                'zones' => [
                    'top' => new Zone(),
                    'left' => new Zone(),
                    'right' => Zone::fromArray(['allowedBlockDefinitions' => ['title', 'list']]),
                    'bottom' => Zone::fromArray(['allowedBlockDefinitions' => ['title']]),
                ],
            ]
        );

        $layoutType2 = LayoutType::fromArray(
            [
                'identifier' => '4_zones_b',
                'zones' => [
                    'top' => new Zone(),
                    'left' => new Zone(),
                    'right' => new Zone(),
                    'bottom' => new Zone(),
                ],
            ]
        );

        return new LayoutTypeRegistry(
            [
                '4_zones_a' => $layoutType1,
                '4_zones_b' => $layoutType2,
            ]
        );
    }

    protected function createItemDefinitionRegistry(): ItemDefinitionRegistryInterface
    {
        $itemConfigHandler = new ItemConfigHandler();
        $itemConfigDefinition = ConfigDefinition::fromArray(
            [
                'parameterDefinitions' => $itemConfigHandler->getParameterDefinitions(),
            ]
        );

        $itemDefinition = ItemDefinition::fromArray(
            [
                'valueType' => 'my_value_type',
                'configDefinitions' => [
                    'key' => $itemConfigDefinition,
                ],
            ]
        );

        return new ItemDefinitionRegistry(['my_value_type' => $itemDefinition]);
    }

    protected function createQueryTypeRegistry(): QueryTypeRegistryInterface
    {
        return new QueryTypeRegistry(['my_query_type' => new QueryType('my_query_type')]);
    }

    protected function createBlockDefinitionRegistry(): BlockDefinitionRegistryInterface
    {
        $configHandler = new BlockConfigHandler();
        $configDefinition = ConfigDefinition::fromArray(
            [
                'parameterDefinitions' => $configHandler->getParameterDefinitions(),
            ]
        );

        $blockDefinitionHandler1 = new BlockDefinitionHandler();
        $blockDefinitionHandler2 = new BlockDefinitionHandlerWithTranslatableParameter();

        $blockDefinition1 = BlockDefinition::fromArray(
            [
                'identifier' => 'title',
                'parameterDefinitions' => $blockDefinitionHandler1->getParameterDefinitions(),
                'configDefinitions' => ['key' => $configDefinition],
                'isTranslatable' => true,
                'viewTypes' => [
                    'small' => ViewType::fromArray(
                        [
                            'itemViewTypes' => [
                                'standard' => new ItemViewType(),
                            ],
                        ]
                    ),
                ],
            ]
        );

        $blockDefinition2 = BlockDefinition::fromArray(
            [
                'identifier' => 'text',
                'parameterDefinitions' => $blockDefinitionHandler1->getParameterDefinitions(),
                'configDefinitions' => ['key' => $configDefinition],
                'isTranslatable' => false,
                'viewTypes' => [
                    'standard' => ViewType::fromArray(
                        [
                            'itemViewTypes' => [
                                'standard' => new ItemViewType(),
                            ],
                        ]
                    ),
                ],
            ]
        );

        $blockDefinition3 = BlockDefinition::fromArray(
            [
                'identifier' => 'gallery',
                'parameterDefinitions' => $blockDefinitionHandler2->getParameterDefinitions(),
                'configDefinitions' => ['key' => $configDefinition],
                'isTranslatable' => false,
                'collections' => ['default' => new Collection()],
                'viewTypes' => [
                    'standard' => ViewType::fromArray(
                        [
                            'itemViewTypes' => [
                                'standard' => new ItemViewType(),
                            ],
                        ]
                    ),
                ],
            ]
        );

        $blockDefinition4 = BlockDefinition::fromArray(
            [
                'identifier' => 'list',
                'parameterDefinitions' => $blockDefinitionHandler2->getParameterDefinitions(),
                'configDefinitions' => ['key' => $configDefinition],
                'isTranslatable' => false,
                'collections' => ['default' => new Collection()],
                'viewTypes' => [
                    'small' => ViewType::fromArray(
                        [
                            'itemViewTypes' => [
                                'standard' => new ItemViewType(),
                            ],
                        ]
                    ),
                ],
            ]
        );

        $blockDefinition5 = ContainerDefinition::fromArray(
            [
                'identifier' => 'column',
                'configDefinitions' => ['key' => $configDefinition],
                'handler' => new ContainerDefinitionHandler([], ['main', 'other']),
                'viewTypes' => [
                    'column' => ViewType::fromArray(
                        [
                            'itemViewTypes' => [
                                'standard' => new ItemViewType(),
                            ],
                        ]
                    ),
                ],
            ]
        );

        $blockDefinition6 = ContainerDefinition::fromArray(
            [
                'identifier' => 'two_columns',
                'configDefinitions' => ['key' => $configDefinition],
                'handler' => new ContainerDefinitionHandler([], ['left', 'right']),
                'viewTypes' => [
                    'two_columns_50_50' => ViewType::fromArray(
                        [
                            'itemViewTypes' => [
                                'standard' => new ItemViewType(),
                            ],
                        ]
                    ),
                ],
            ]
        );

        return new BlockDefinitionRegistry(
            [
                'title' => $blockDefinition1,
                'text' => $blockDefinition2,
                'gallery' => $blockDefinition3,
                'list' => $blockDefinition4,
                'column' => $blockDefinition5,
                'two_columns' => $blockDefinition6,
            ]
        );
    }

    protected function createTargetTypeRegistry(): TargetTypeRegistryInterface
    {
        return new TargetTypeRegistry(
            [
                new TargetType1(),
                new TargetType\Route(),
                new TargetType\RoutePrefix(),
                new TargetType\PathInfo(),
                new TargetType\PathInfoPrefix(),
                new TargetType\RequestUri(),
                new TargetType\RequestUriPrefix(),
            ]
        );
    }

    protected function createConditionTypeRegistry(): ConditionTypeRegistryInterface
    {
        return new ConditionTypeRegistry(
            [
                new ConditionType1(),
                new ConditionType\RouteParameter(),
            ]
        );
    }

    protected function createParameterTypeRegistry(): ParameterTypeRegistryInterface
    {
        $remoteIdConverter = new RemoteIdConverter($this->cmsItemLoaderMock);

        return new ParameterTypeRegistry(
            [
                new ParameterType\TextLineType(),
                new ParameterType\TextType(),
                new ParameterType\UrlType(),
                new ParameterType\RangeType(),
                new ParameterType\NumberType(),
                new ParameterType\LinkType(new ValueTypeRegistry([]), $remoteIdConverter),
                new ParameterType\ItemLinkType(new ValueTypeRegistry([]), $remoteIdConverter),
                new ParameterType\IntegerType(),
                new ParameterType\IdentifierType(),
                new ParameterType\HtmlType(new HtmlPurifier()),
                new ParameterType\EmailType(),
                new ParameterType\ChoiceType(),
                new ParameterType\BooleanType(),
                new ParameterType\DateTimeType(),
                new ParameterType\Compound\BooleanType(),
            ]
        );
    }
}
