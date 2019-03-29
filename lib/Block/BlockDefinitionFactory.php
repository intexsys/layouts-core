<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Block;

use Netgen\BlockManager\Block\BlockDefinition\BlockDefinitionHandlerInterface;
use Netgen\BlockManager\Block\BlockDefinition\Configuration\Collection;
use Netgen\BlockManager\Block\BlockDefinition\Configuration\Form;
use Netgen\BlockManager\Block\BlockDefinition\Configuration\ItemViewType;
use Netgen\BlockManager\Block\BlockDefinition\Configuration\ViewType;
use Netgen\BlockManager\Block\BlockDefinition\ContainerDefinitionHandlerInterface;
use Netgen\BlockManager\Block\BlockDefinition\TwigBlockDefinitionHandlerInterface;
use Netgen\BlockManager\Block\Registry\HandlerPluginRegistryInterface;
use Netgen\BlockManager\Config\ConfigDefinitionFactory;
use Netgen\BlockManager\Exception\RuntimeException;
use Netgen\BlockManager\Parameters\ParameterBuilderFactoryInterface;

final class BlockDefinitionFactory
{
    /**
     * @var \Netgen\BlockManager\Parameters\ParameterBuilderFactoryInterface
     */
    private $parameterBuilderFactory;

    /**
     * @var \Netgen\BlockManager\Block\Registry\HandlerPluginRegistryInterface
     */
    private $handlerPluginRegistry;

    /**
     * @var \Netgen\BlockManager\Config\ConfigDefinitionFactory
     */
    private $configDefinitionFactory;

    public function __construct(
        ParameterBuilderFactoryInterface $parameterBuilderFactory,
        HandlerPluginRegistryInterface $handlerPluginRegistry,
        ConfigDefinitionFactory $configDefinitionFactory
    ) {
        $this->parameterBuilderFactory = $parameterBuilderFactory;
        $this->handlerPluginRegistry = $handlerPluginRegistry;
        $this->configDefinitionFactory = $configDefinitionFactory;
    }

    /**
     * Builds the block definition.
     *
     * @param string $identifier
     * @param \Netgen\BlockManager\Block\BlockDefinition\BlockDefinitionHandlerInterface $handler
     * @param array $config
     * @param \Netgen\BlockManager\Config\ConfigDefinitionHandlerInterface[] $configDefinitionHandlers
     *
     * @return \Netgen\BlockManager\Block\BlockDefinitionInterface
     */
    public function buildBlockDefinition(
        string $identifier,
        BlockDefinitionHandlerInterface $handler,
        array $config,
        array $configDefinitionHandlers
    ): BlockDefinitionInterface {
        $commonData = $this->getCommonBlockDefinitionData(
            $identifier,
            $handler,
            $config,
            $configDefinitionHandlers
        );

        return BlockDefinition::fromArray($commonData);
    }

    /**
     * Builds the block definition.
     *
     * @param string $identifier
     * @param \Netgen\BlockManager\Block\BlockDefinition\TwigBlockDefinitionHandlerInterface $handler
     * @param array $config
     * @param \Netgen\BlockManager\Config\ConfigDefinitionHandlerInterface[] $configDefinitionHandlers
     *
     * @return \Netgen\BlockManager\Block\TwigBlockDefinitionInterface
     */
    public function buildTwigBlockDefinition(
        string $identifier,
        TwigBlockDefinitionHandlerInterface $handler,
        array $config,
        array $configDefinitionHandlers
    ): TwigBlockDefinitionInterface {
        $commonData = $this->getCommonBlockDefinitionData(
            $identifier,
            $handler,
            $config,
            $configDefinitionHandlers
        );

        return TwigBlockDefinition::fromArray($commonData);
    }

    /**
     * Builds the container definition.
     *
     * @param string $identifier
     * @param \Netgen\BlockManager\Block\BlockDefinition\ContainerDefinitionHandlerInterface $handler
     * @param array $config
     * @param \Netgen\BlockManager\Config\ConfigDefinitionHandlerInterface[] $configDefinitionHandlers
     *
     * @return \Netgen\BlockManager\Block\ContainerDefinitionInterface
     */
    public function buildContainerDefinition(
        string $identifier,
        ContainerDefinitionHandlerInterface $handler,
        array $config,
        array $configDefinitionHandlers
    ): ContainerDefinitionInterface {
        $commonData = $this->getCommonBlockDefinitionData(
            $identifier,
            $handler,
            $config,
            $configDefinitionHandlers
        );

        return ContainerDefinition::fromArray($commonData);
    }

    /**
     * Returns the data common to all block definition types.
     *
     * @param string $identifier
     * @param \Netgen\BlockManager\Block\BlockDefinition\BlockDefinitionHandlerInterface $handler
     * @param array $config
     * @param \Netgen\BlockManager\Config\ConfigDefinitionHandlerInterface[] $configDefinitionHandlers
     *
     * @return array
     */
    private function getCommonBlockDefinitionData(
        string $identifier,
        BlockDefinitionHandlerInterface $handler,
        array $config,
        array $configDefinitionHandlers
    ): array {
        $parameterBuilder = $this->parameterBuilderFactory->createParameterBuilder();
        $handler->buildParameters($parameterBuilder);

        $handlerPlugins = $this->handlerPluginRegistry->getPlugins(get_class($handler));
        foreach ($handlerPlugins as $handlerPlugin) {
            $handlerPlugin->buildParameters($parameterBuilder);
        }

        $parameterDefinitions = $parameterBuilder->buildParameterDefinitions();

        $configDefinitions = [];
        foreach ($configDefinitionHandlers as $configKey => $configDefinitionHandler) {
            $configDefinitions[$configKey] = $this->configDefinitionFactory->buildConfigDefinition(
                $configKey,
                $configDefinitionHandler
            );
        }

        return [
            'identifier' => $identifier,
            'handler' => $handler,
            'handlerPlugins' => $handlerPlugins,
            'parameterDefinitions' => $parameterDefinitions,
            'configDefinitions' => $configDefinitions,
        ] + $this->processConfig($identifier, $config);
    }

    /**
     * Processes and returns the block definition configuration.
     */
    private function processConfig(string $identifier, array $config): array
    {
        $collections = [];
        $forms = [];
        $viewTypes = [];

        if (isset($config['collections'])) {
            foreach ($config['collections'] as $collectionIdentifier => $collectionConfig) {
                $collections[$collectionIdentifier] = Collection::fromArray(
                    [
                        'identifier' => $collectionIdentifier,
                        'validItemTypes' => $collectionConfig['valid_item_types'],
                        'validQueryTypes' => $collectionConfig['valid_query_types'],
                    ]
                );
            }
        }

        if (isset($config['forms'])) {
            foreach ($config['forms'] as $formIdentifier => $formConfig) {
                if (!$formConfig['enabled']) {
                    continue;
                }

                $forms[$formIdentifier] = Form::fromArray(
                    [
                        'identifier' => $formIdentifier,
                        'type' => $formConfig['type'],
                    ]
                );
            }
        }

        if (isset($config['view_types'])) {
            foreach ($config['view_types'] as $viewTypeIdentifier => $viewTypeConfig) {
                if (!$viewTypeConfig['enabled']) {
                    continue;
                }

                $itemViewTypes = [];

                if (!is_array($viewTypeConfig['item_view_types'] ?? [])) {
                    $viewTypeConfig['item_view_types'] = [];
                }

                if (!isset($viewTypeConfig['item_view_types']['standard'])) {
                    $viewTypeConfig['item_view_types']['standard'] = [
                        'name' => 'Standard',
                        'enabled' => true,
                    ];
                }

                foreach ($viewTypeConfig['item_view_types'] as $itemViewTypeIdentifier => $itemViewTypeConfig) {
                    if (!$itemViewTypeConfig['enabled']) {
                        continue;
                    }

                    $itemViewTypes[$itemViewTypeIdentifier] = ItemViewType::fromArray(
                        [
                            'identifier' => $itemViewTypeIdentifier,
                            'name' => $itemViewTypeConfig['name'],
                        ]
                    );
                }

                if (count($itemViewTypes) === 0) {
                    throw new RuntimeException(
                        sprintf(
                            'You need to specify at least one enabled item view type for "%s" view type and "%s" block definition.',
                            $viewTypeIdentifier,
                            $identifier
                        )
                    );
                }

                $viewTypes[$viewTypeIdentifier] = ViewType::fromArray(
                    [
                        'identifier' => $viewTypeIdentifier,
                        'name' => $viewTypeConfig['name'] ?? '',
                        'itemViewTypes' => $itemViewTypes,
                        'validParameters' => $viewTypeConfig['valid_parameters'] ?? null,
                    ]
                );
            }
        }

        if (count($viewTypes) === 0) {
            throw new RuntimeException(
                sprintf(
                    'You need to specify at least one enabled view type for "%s" block definition.',
                    $identifier
                )
            );
        }

        return [
            'name' => $config['name'] ?? '',
            'icon' => $config['icon'] ?? '',
            'isTranslatable' => $config['translatable'] ?? false,
            'collections' => $collections,
            'forms' => $forms,
            'viewTypes' => $viewTypes,
        ];
    }
}
