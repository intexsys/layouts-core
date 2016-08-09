<?php

namespace Netgen\Bundle\BlockManagerBundle\DependencyInjection;

use Netgen\BlockManager\Block\Form\ContentEditType;
use Netgen\BlockManager\Block\Form\DesignEditType;
use Netgen\BlockManager\Block\Form\FullEditType;
use Netgen\BlockManager\Collection\Query\Form\FullEditType as QueryFullEditType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    protected $alias;

    /**
     * @var \Closure[]
     */
    protected $externalConfigTreeBuilders = array();

    /**
     * Constructor.
     *
     * @param string $alias
     * @param \Closure[] $externalConfigTreeBuilders
     */
    public function __construct($alias, array $externalConfigTreeBuilders = array())
    {
        $this->alias = $alias;
        $this->externalConfigTreeBuilders = $externalConfigTreeBuilders;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root($this->alias);
        $children = $rootNode->children();

        foreach ($this->getAvailableNodeDefinitions() as $nodeDefinition) {
            $children->append($nodeDefinition);
        }

        foreach ($this->externalConfigTreeBuilders as $externalConfigTreeBuilder) {
            $externalConfigTreeBuilder($rootNode, $this);
        }

        $children->end();

        return $treeBuilder;
    }

    /**
     * Returns various semantic configuration for the bundle.
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition[]
     */
    public function getAvailableNodeDefinitions()
    {
        return array(
            $this->getViewNodeDefinition(),
            $this->getBlockDefinitionsNodeDefinition(),
            $this->getBlockTypesNodeDefinition(),
            $this->getBlockTypeGroupsNodeDefinition(),
            $this->getLayoutTypesNodeDefinition(),
            $this->getSourcesNodeDefinition(),
            $this->getQueryTypesNodeDefinition(),
            $this->getPagelayoutNodeDefinition(),
            $this->getGoogleMapsNodeDefinition(),
        );
    }

    /**
     * Returns node definition for template resolvers.
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    public function getViewNodeDefinition()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('view');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('view')
            ->prototype('array')
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('context')
                ->prototype('array')
                    ->useAttributeAsKey('config')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->performNoDeepMerging()
                        ->children()
                            ->scalarNode('template')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->arrayNode('match')
                                ->isRequired()
                                ->prototype('scalar')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Returns node definition for block definitions.
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    public function getBlockDefinitionsNodeDefinition()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('block_definitions');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('identifier')
            ->prototype('array')
                ->children()
                    ->arrayNode('forms')
                        ->addDefaultsIfNotSet()
                        ->validate()
                            ->always(function ($v) {
                                $exception = new InvalidConfigurationException('Block definition must either have a full form or content and design forms.');

                                if ($v['full']['enabled'] && ($v['design']['enabled'] || $v['content']['enabled'])) {
                                    throw $exception;
                                }

                                if (!$v['full']['enabled']) {
                                    if ($v['design']['enabled'] && !$v['content']['enabled']) {
                                        throw $exception;
                                    }

                                    if (!$v['design']['enabled'] && $v['content']['enabled']) {
                                        throw $exception;
                                    }
                                }

                                return $v;
                            })
                        ->end()
                        ->children()
                            ->arrayNode('full')
                                ->addDefaultsIfNotSet()
                                ->canBeDisabled()
                                ->children()
                                    ->scalarNode('type')
                                        ->treatNullLike(FullEditType::class)
                                        ->defaultValue(FullEditType::class)
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('design')
                                ->addDefaultsIfNotSet()
                                ->canBeEnabled()
                                ->children()
                                    ->scalarNode('type')
                                        ->treatNullLike(DesignEditType::class)
                                        ->defaultValue(DesignEditType::class)
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->arrayNode('parameters')
                                        ->validate()
                                            ->always(function ($v) {
                                                return array_values(array_unique($v));
                                            })
                                        ->end()
                                        ->prototype('scalar')
                                            ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('content')
                                ->addDefaultsIfNotSet()
                                ->canBeEnabled()
                                ->children()
                                    ->scalarNode('type')
                                        ->treatNullLike(ContentEditType::class)
                                        ->defaultValue(ContentEditType::class)
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->arrayNode('parameters')
                                        ->validate()
                                            ->always(function ($v) {
                                                return array_values(array_unique($v));
                                            })
                                        ->end()
                                        ->prototype('scalar')
                                            ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('view_types')
                        ->isRequired()
                        ->performNoDeepMerging()
                        ->requiresAtLeastOneElement()
                        ->useAttributeAsKey('view_type')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('name')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->arrayNode('item_view_types')
                                    ->defaultValue(array('standard' => array('name' => 'Standard')))
                                    ->performNoDeepMerging()
                                    ->requiresAtLeastOneElement()
                                    ->useAttributeAsKey('item_view_type')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('name')
                                                ->isRequired()
                                                ->cannotBeEmpty()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Returns node definition for query types.
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    public function getQueryTypesNodeDefinition()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('query_types');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('identifier')
            ->prototype('array')
                ->children()
                    ->scalarNode('name')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('forms')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('full')
                                ->canBeDisabled()
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('type')
                                        ->treatNullLike(QueryFullEditType::class)
                                        ->defaultValue(QueryFullEditType::class)
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('defaults')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('parameters')
                                ->defaultValue(array())
                                ->performNoDeepMerging()
                                ->requiresAtLeastOneElement()
                                ->useAttributeAsKey('parameter')
                                ->prototype('variable')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Returns node definition for block types.
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    public function getBlockTypesNodeDefinition()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('block_types');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('identifier')
            ->prototype('array')
                ->canBeDisabled()
                ->children()
                    ->scalarNode('name')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('definition_identifier')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('defaults')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('name')
                                ->defaultValue('')
                            ->end()
                            ->scalarNode('view_type')
                                ->defaultValue('')
                            ->end()
                            ->scalarNode('item_view_type')
                                ->defaultValue('')
                            ->end()
                            ->arrayNode('parameters')
                                ->defaultValue(array())
                                ->performNoDeepMerging()
                                ->requiresAtLeastOneElement()
                                ->useAttributeAsKey('parameter')
                                ->prototype('variable')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Returns node definition for block type groups.
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    public function getBlockTypeGroupsNodeDefinition()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('block_type_groups');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('identifier')
            ->prototype('array')
                ->canBeDisabled()
                ->children()
                    ->scalarNode('name')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('block_types')
                        ->validate()
                            ->always(function ($v) {
                                return array_values(array_unique($v));
                            })
                        ->end()
                        ->performNoDeepMerging()
                        ->requiresAtLeastOneElement()
                        ->prototype('scalar')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Returns node definition for layouts.
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    public function getLayoutTypesNodeDefinition()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('layout_types');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('type')
            ->prototype('array')
                ->canBeDisabled()
                ->children()
                    ->scalarNode('name')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('zones')
                        ->isRequired()
                        ->performNoDeepMerging()
                        ->requiresAtLeastOneElement()
                        ->prototype('array')
                            ->children()
                                ->scalarNode('name')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->arrayNode('allowed_block_definitions')
                                    ->validate()
                                        ->always(function ($v) {
                                            return array_values(array_unique($v));
                                        })
                                    ->end()
                                    ->requiresAtLeastOneElement()
                                    ->prototype('scalar')
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Returns node definition for sources.
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    public function getSourcesNodeDefinition()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('sources');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('source')
            ->prototype('array')
                ->canBeDisabled()
                ->beforeNormalization()
                    ->ifTrue(function ($v) { return is_array($v) && !array_key_exists('queries', $v); })
                    ->then(function ($v) {
                        // Key that should not be rewritten to the query config
                        $excludedKeys = array('name' => true);
                        $query = array();
                        foreach ($v as $key => $value) {
                            if (isset($excludedKeys[$key])) {
                                continue;
                            }
                            $query[$key] = $v[$key];
                            unset($v[$key]);
                        }
                        $v['queries'] = array('default' => $query);

                        return $v;
                    })
                ->end()
                ->children()
                    ->scalarNode('name')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('queries')
                        ->isRequired()
                        ->performNoDeepMerging()
                        ->requiresAtLeastOneElement()
                        ->prototype('array')
                            ->children()
                                ->scalarNode('query_type')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->arrayNode('default_parameters')
                                    ->defaultValue(array())
                                    ->performNoDeepMerging()
                                    ->requiresAtLeastOneElement()
                                    ->useAttributeAsKey('parameter')
                                    ->prototype('variable')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Returns node definition for pagelayout.
     *
     * @return \Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition
     */
    public function getPagelayoutNodeDefinition()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('pagelayout', 'scalar');

        $node
            ->defaultValue('NetgenBlockManagerBundle::empty_pagelayout.html.twig')
            ->cannotBeEmpty();

        return $node;
    }

    /**
     * Returns node definition for Google Maps.
     *
     * @return \Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition
     */
    public function getGoogleMapsNodeDefinition()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('google_maps_api_key', 'scalar');

        $node->defaultValue('');

        return $node;
    }
}
