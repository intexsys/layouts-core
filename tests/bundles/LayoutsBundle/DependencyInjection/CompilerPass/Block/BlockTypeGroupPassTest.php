<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Tests\DependencyInjection\CompilerPass\Block;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\DependencyInjection\Reference;

final class BlockTypeGroupPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::buildBlockTypeGroups
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::generateBlockTypeGroupConfig
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::process
     */
    public function testProcess(): void
    {
        $this->setParameter(
            'netgen_layouts.block_type_groups',
            [
                'test' => [
                    'enabled' => true,
                    'block_types' => [],
                ],
            ]
        );

        $this->setParameter('netgen_layouts.block_types', []);

        $this->setDefinition('netgen_layouts.block.registry.block_type_group', new Definition(null, [[]]));

        $this->compile();

        $this->assertContainerBuilderHasService('netgen_layouts.block.block_type_group.test');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'netgen_layouts.block.registry.block_type_group',
            0,
            [
                'test' => new Reference('netgen_layouts.block.block_type_group.test'),
            ]
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::buildBlockTypeGroups
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::generateBlockTypeGroupConfig
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::process
     */
    public function testProcessWithNoBlockType(): void
    {
        $this->setParameter(
            'netgen_layouts.block_type_groups',
            [
                'test' => [
                    'enabled' => true,
                    'block_types' => ['test1', 'test2'],
                ],
            ]
        );

        $this->setParameter(
            'netgen_layouts.block_types',
            [
                'test1' => [
                    'enabled' => true,
                ],
            ]
        );

        $this->setDefinition('netgen_layouts.block.block_type.test1', new Definition());
        $this->setDefinition('netgen_layouts.block.registry.block_type_group', new Definition(null, [[]]));

        $this->compile();

        $this->assertContainerBuilderHasService('netgen_layouts.block.block_type_group.test');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'netgen_layouts.block.block_type_group.test',
            2,
            [
                new Reference('netgen_layouts.block.block_type.test1'),
            ]
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::buildBlockTypeGroups
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::generateBlockTypeGroupConfig
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::process
     */
    public function testProcessWithPopulatingCustomGroup(): void
    {
        $this->setParameter(
            'netgen_layouts.block_type_groups',
            [
                'test' => [
                    'enabled' => true,
                    'block_types' => ['test1'],
                ],
                'custom' => [
                    'enabled' => true,
                    'block_types' => [],
                ],
            ]
        );

        $this->setParameter(
            'netgen_layouts.block_types',
            [
                'test1' => [
                    'enabled' => true,
                    'definition_identifier' => 'test',
                ],
                'test2' => [
                    'enabled' => false,
                    'definition_identifier' => 'test',
                ],
                'test3' => [
                    'enabled' => true,
                    'definition_identifier' => 'test',
                ],
            ]
        );

        $this->setDefinition('netgen_layouts.block.block_type.test1', new Definition());
        $this->setDefinition('netgen_layouts.block.block_type.test2', new Definition());
        $this->setDefinition('netgen_layouts.block.block_type.test3', new Definition());
        $this->setDefinition('netgen_layouts.block.registry.block_type_group', new Definition(null, [[]]));

        $this->compile();

        $blockTypeGroups = $this->container->getParameter('netgen_layouts.block_type_groups');
        self::assertArrayHasKey('custom', $blockTypeGroups);

        self::assertSame(
            [
                'enabled' => true,
                'block_types' => ['test2', 'test3'],
            ],
            $blockTypeGroups['custom']
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::buildBlockTypeGroups
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::generateBlockTypeGroupConfig
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::process
     */
    public function testProcessWithDisabledGroup(): void
    {
        $this->setParameter(
            'netgen_layouts.block_type_groups',
            [
                'test' => [
                    'enabled' => false,
                    'block_types' => [],
                ],
            ]
        );

        $this->setParameter('netgen_layouts.block_types', []);

        $this->setDefinition('netgen_layouts.block.registry.block_type_group', new Definition(null, [[]]));

        $this->compile();

        $blockTypeGroups = $this->container->getParameter('netgen_layouts.block_type_groups');
        self::assertArrayHasKey('test', $blockTypeGroups);

        self::assertSame(
            [
                'enabled' => false,
                'block_types' => [],
            ],
            $blockTypeGroups['test']
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\Block\BlockTypeGroupPass::process
     */
    public function testProcessWithEmptyContainer(): void
    {
        $this->compile();

        self::assertInstanceOf(FrozenParameterBag::class, $this->container->getParameterBag());
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new BlockTypeGroupPass());
    }
}
