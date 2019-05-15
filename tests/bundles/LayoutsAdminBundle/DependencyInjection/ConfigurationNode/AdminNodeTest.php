<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\Tests\DependencyInjection\ConfigurationNode;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Netgen\Bundle\BlockManagerBundle\DependencyInjection\Configuration;
use Netgen\Bundle\BlockManagerBundle\DependencyInjection\NetgenBlockManagerExtension;
use Netgen\Bundle\LayoutsAdminBundle\DependencyInjection\ExtensionPlugin;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class AdminNodeTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\DependencyInjection\ConfigurationNode\AdminNode::getConfigurationNode
     */
    public function testJavascripts(): void
    {
        $config = [
            [
                'admin' => [
                    'javascripts' => [
                        'script',
                    ],
                ],
            ],
        ];

        $expectedConfig = [
            'admin' => [
                'javascripts' => [
                    'script',
                ],
            ],
        ];

        $this->assertProcessedConfigurationEquals(
            $config,
            $expectedConfig,
            'admin.javascripts'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\DependencyInjection\ConfigurationNode\AdminNode::getConfigurationNode
     */
    public function testJavascriptsWithNoJavascripts(): void
    {
        $config = [
            [
                'admin' => [],
            ],
        ];

        $expectedConfig = [
            'admin' => [
                'javascripts' => [],
            ],
        ];

        $this->assertProcessedConfigurationEquals(
            $config,
            $expectedConfig,
            'admin.javascripts'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\DependencyInjection\ConfigurationNode\AdminNode::getConfigurationNode
     */
    public function testJavascriptsWithInvalidJavascripts(): void
    {
        $config = [
            [
                'admin' => [
                    'javascripts' => 'script',
                ],
            ],
        ];

        $this->assertConfigurationIsInvalid($config);
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\DependencyInjection\ConfigurationNode\AdminNode::getConfigurationNode
     */
    public function testJavascriptsWithInvalidJavascript(): void
    {
        $config = [
            [
                'admin' => [
                    'javascripts' => [
                        42,
                    ],
                ],
            ],
        ];

        $this->assertConfigurationIsInvalid($config, 'The value should be a string');
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\DependencyInjection\ConfigurationNode\AdminNode::getConfigurationNode
     */
    public function testStylesheets(): void
    {
        $config = [
            [
                'admin' => [
                    'stylesheets' => [
                        'script',
                    ],
                ],
            ],
        ];

        $expectedConfig = [
            'admin' => [
                'stylesheets' => [
                    'script',
                ],
            ],
        ];

        $this->assertProcessedConfigurationEquals(
            $config,
            $expectedConfig,
            'admin.stylesheets'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\DependencyInjection\ConfigurationNode\AdminNode::getConfigurationNode
     */
    public function testStylesheetsWithNoStylesheets(): void
    {
        $config = [
            [
                'admin' => [],
            ],
        ];

        $expectedConfig = [
            'admin' => [
                'stylesheets' => [],
            ],
        ];

        $this->assertProcessedConfigurationEquals(
            $config,
            $expectedConfig,
            'admin.stylesheets'
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\DependencyInjection\ConfigurationNode\AdminNode::getConfigurationNode
     */
    public function testStylesheetsWithInvalidStylesheets(): void
    {
        $config = [
            [
                'admin' => [
                    'stylesheets' => 'script',
                ],
            ],
        ];

        $this->assertConfigurationIsInvalid($config);
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\DependencyInjection\ConfigurationNode\AdminNode::getConfigurationNode
     */
    public function testStylesheetsWithInvalidStylesheet(): void
    {
        $config = [
            [
                'admin' => [
                    'stylesheets' => [
                        42,
                    ],
                ],
            ],
        ];

        $this->assertConfigurationIsInvalid($config, 'The value should be a string');
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        $extension = new NetgenBlockManagerExtension();
        $extension->addPlugin(new ExtensionPlugin());

        return new Configuration($extension);
    }
}
