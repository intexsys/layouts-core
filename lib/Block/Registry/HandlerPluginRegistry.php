<?php

declare(strict_types=1);

namespace Netgen\Layouts\Block\Registry;

use Netgen\Layouts\Block\BlockDefinition\Handler\PluginInterface;
use function array_filter;
use function array_values;
use function is_a;

final class HandlerPluginRegistry
{
    /**
     * @var \Netgen\Layouts\Block\BlockDefinition\Handler\PluginInterface[]
     */
    private $handlerPlugins = [];

    /**
     * @param iterable<\Netgen\Layouts\Block\BlockDefinition\Handler\PluginInterface> $handlerPlugins
     */
    public function __construct(iterable $handlerPlugins)
    {
        foreach ($handlerPlugins as $handlerPlugin) {
            if ($handlerPlugin instanceof PluginInterface) {
                $this->handlerPlugins[] = $handlerPlugin;
            }
        }
    }

    /**
     * Returns all handler plugins for the provided handler class.
     *
     * @param string $handlerClass
     *
     * @return \Netgen\Layouts\Block\BlockDefinition\Handler\PluginInterface[]
     */
    public function getPlugins(string $handlerClass): array
    {
        return array_values(
            array_filter(
                $this->handlerPlugins,
                static function (PluginInterface $plugin) use ($handlerClass): bool {
                    foreach ($plugin::getExtendedHandlers() as $extendedHandler) {
                        if (is_a($handlerClass, $extendedHandler, true)) {
                            return true;
                        }
                    }

                    return false;
                }
            )
        );
    }
}
