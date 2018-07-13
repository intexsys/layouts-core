<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Core\Values\Config;

use Netgen\BlockManager\API\Values\Config\Config as APIConfig;
use Netgen\BlockManager\Config\ConfigDefinitionInterface;
use Netgen\BlockManager\Core\Values\ParameterCollectionTrait;
use Netgen\BlockManager\Value;

final class Config extends Value implements APIConfig
{
    use ParameterCollectionTrait;

    /**
     * @var string
     */
    private $configKey;

    /**
     * @var \Netgen\BlockManager\Config\ConfigDefinitionInterface
     */
    private $definition;

    public function getConfigKey(): string
    {
        return $this->configKey;
    }

    public function getDefinition(): ConfigDefinitionInterface
    {
        return $this->definition;
    }
}
