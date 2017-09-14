<?php

namespace Netgen\BlockManager\Block\Registry;

use Netgen\BlockManager\Block\BlockDefinition\Handler\PluginInterface;

interface HandlerPluginRegistryInterface
{
    /**
     * Adds a block definition handler plugin to registry.
     *
     * @param \Netgen\BlockManager\Block\BlockDefinition\Handler\PluginInterface $plugin
     */
    public function addPlugin(PluginInterface $plugin);

    /**
     * Returns all handler plugins for the provided handler class.
     *
     * @param string $handlerClass
     *
     * @return \Netgen\BlockManager\Block\BlockDefinition\Handler\PluginInterface[]
     */
    public function getPlugins($handlerClass);
}
