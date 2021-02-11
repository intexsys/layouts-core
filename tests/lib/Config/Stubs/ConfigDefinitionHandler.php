<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Config\Stubs;

use Netgen\Layouts\Config\ConfigDefinitionHandlerInterface;
use Netgen\Layouts\Parameters\ParameterBuilderInterface;
use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\Parameters\ParameterType;

final class ConfigDefinitionHandler implements ConfigDefinitionHandlerInterface
{
    /**
     * @return array<string, \Netgen\Layouts\Parameters\ParameterDefinition>
     */
    public function getParameterDefinitions(): array
    {
        return [
            'param' => ParameterDefinition::fromArray(
                [
                    'name' => 'param',
                    'type' => new ParameterType\TextLineType(),
                    'isRequired' => false,
                ]
            ),
            'param2' => ParameterDefinition::fromArray(
                [
                    'name' => 'param2',
                    'type' => new ParameterType\TextLineType(),
                    'isRequired' => false,
                ]
            ),
        ];
    }

    public function buildParameters(ParameterBuilderInterface $builder): void
    {
    }
}
