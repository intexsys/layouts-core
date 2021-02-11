<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\DependencyInjection\ConfigurationNode;

use Netgen\Bundle\LayoutsBundle\DependencyInjection\ConfigurationNodeInterface;
use Netgen\Layouts\Utils\BackwardsCompatibility\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use function is_string;

final class AppNode implements ConfigurationNodeInterface
{
    public function getConfigurationNode(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('app');
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('javascripts')
                    ->scalarPrototype()
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(
                                static fn ($v): bool => !is_string($v)
                            )
                            ->thenInvalid('The value should be a string')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('stylesheets')
                    ->scalarPrototype()
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(
                                static fn ($v): bool => !is_string($v)
                            )
                            ->thenInvalid('The value should be a string')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
}
