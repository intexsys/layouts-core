<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\LayoutResolver\Form;

use Netgen\Bundle\LayoutsBundle\DependencyInjection\CompilerPass\DefinitionClassTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class TargetTypePass implements CompilerPassInterface
{
    use DefinitionClassTrait;

    private const SERVICE_NAME = 'netgen_layouts.layout.resolver.form.target_type';
    private const TAG_NAME = 'netgen_block_manager.layout.resolver.form.target_type.mapper';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(self::SERVICE_NAME)) {
            return;
        }

        $formType = $container->findDefinition(self::SERVICE_NAME);
        $mapperServices = $container->findTaggedServiceIds(self::TAG_NAME);

        $mappers = [];
        foreach ($mapperServices as $mapperService => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['target_type'])) {
                    $mappers[$tag['target_type']] = new Reference($mapperService);

                    continue 2;
                }
            }

            $mapperClass = $this->getDefinitionClass($container, $mapperService);
            if (isset($mapperClass::$defaultTargetType)) {
                $mappers[$mapperClass::$defaultTargetType] = new Reference($mapperService);

                continue;
            }
        }

        $formType->replaceArgument(0, $mappers);
    }
}
