<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Tests\Templating\Plugin;

use Netgen\Bundle\LayoutsBundle\Templating\Plugin\PluginCollection;
use Netgen\Bundle\LayoutsBundle\Templating\Plugin\SimplePlugin;
use PHPUnit\Framework\TestCase;

final class PluginCollectionTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\LayoutsBundle\Templating\Plugin\PluginInterface
     */
    private $plugin1;

    /**
     * @var \Netgen\Bundle\LayoutsBundle\Templating\Plugin\PluginInterface
     */
    private $plugin2;

    /**
     * @var \Netgen\Bundle\LayoutsBundle\Templating\Plugin\PluginCollection
     */
    private $pluginCollection;

    public function setUp(): void
    {
        $this->plugin1 = new SimplePlugin('template1.html.twig');
        $this->plugin2 = new SimplePlugin('template2.html.twig');

        $this->pluginCollection = new PluginCollection(
            'plugin',
            [
                $this->plugin1,
                $this->plugin2,
            ]
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Templating\Plugin\PluginCollection::__construct
     * @covers \Netgen\Bundle\LayoutsBundle\Templating\Plugin\PluginCollection::getPluginName
     */
    public function testGetPluginName(): void
    {
        self::assertSame('plugin', $this->pluginCollection->getPluginName());
    }

    /**
     * @covers \Netgen\Bundle\LayoutsBundle\Templating\Plugin\PluginCollection::getPlugins
     */
    public function testGetPlugins(): void
    {
        self::assertSame([$this->plugin1, $this->plugin2], $this->pluginCollection->getPlugins());
    }
}
