<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Block\Stubs;

use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\Block\BlockDefinition\BlockDefinitionHandler as BaseBlockDefinitionHandler;
use Netgen\Layouts\Block\DynamicParameters;
use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\Parameters\ParameterType;

final class BlockDefinitionHandlerWithTranslatableParameter extends BaseBlockDefinitionHandler
{
    /**
     * @var array
     */
    private $parameterGroups;

    public function __construct(array $parameterGroups = [])
    {
        $this->parameterGroups = $parameterGroups;
    }

    public function getParameterDefinitions(): array
    {
        return [
            'css_class' => ParameterDefinition::fromArray(
                [
                    'name' => 'css_class',
                    'type' => new ParameterType\TextLineType(),
                    'defaultValue' => 'some-class',
                    'groups' => $this->parameterGroups,
                    'options' => [
                        'translatable' => true,
                    ],
                ]
            ),
            'css_id' => ParameterDefinition::fromArray(
                [
                    'name' => 'css_id',
                    'type' => new ParameterType\TextLineType(),
                    'groups' => $this->parameterGroups,
                    'options' => [
                        'translatable' => false,
                    ],
                ]
            ),
        ];
    }

    public function getDynamicParameters(DynamicParameters $params, Block $block): void
    {
        $params['definition_param'] = 'definition_value';
    }
}
