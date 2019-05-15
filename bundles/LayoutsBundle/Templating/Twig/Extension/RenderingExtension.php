<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Templating\Twig\Extension;

use Netgen\Bundle\LayoutsBundle\Templating\Twig\NodeVisitor\DefaultContext as DefaultContextVisitor;
use Netgen\Bundle\LayoutsBundle\Templating\Twig\Runtime\RenderingRuntime;
use Netgen\Bundle\LayoutsBundle\Templating\Twig\TokenParser\DefaultContext as DefaultContextParser;
use Netgen\Bundle\LayoutsBundle\Templating\Twig\TokenParser\RenderZone as RenderZoneParser;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RenderingExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ngbm_render_item',
                [RenderingRuntime::class, 'renderItem'],
                [
                    'needs_context' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'ngbm_render_layout',
                [RenderingRuntime::class, 'renderValue'],
                [
                    'needs_context' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'ngbm_render_parameter',
                [RenderingRuntime::class, 'renderValue'],
                [
                    'needs_context' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'ngbm_render_block',
                [RenderingRuntime::class, 'renderBlock'],
                [
                    'needs_context' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'ngbm_render_placeholder',
                [RenderingRuntime::class, 'renderPlaceholder'],
                [
                    'needs_context' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'ngbm_render_rule',
                [RenderingRuntime::class, 'renderValue'],
                [
                    'needs_context' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'ngbm_render_rule_target',
                [RenderingRuntime::class, 'renderValue'],
                [
                    'needs_context' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'ngbm_render_rule_condition',
                [RenderingRuntime::class, 'renderValue'],
                [
                    'needs_context' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'ngbm_render_value',
                [RenderingRuntime::class, 'renderValue'],
                [
                    'needs_context' => true,
                    'is_safe' => ['html'],
                ]
            ),
            new TwigFunction(
                'ngbm_render_template',
                [RenderingRuntime::class, 'renderStringTemplate'],
                [
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }

    public function getNodeVisitors(): array
    {
        return [new DefaultContextVisitor()];
    }

    public function getTokenParsers(): array
    {
        return [new RenderZoneParser(), new DefaultContextParser()];
    }
}
