<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Parameters\Form\Mapper;

use Netgen\BlockManager\Parameters\Form\Mapper;
use Netgen\BlockManager\Parameters\ParameterDefinition;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class ChoiceMapper extends Mapper
{
    public function getFormType(): string
    {
        return ChoiceType::class;
    }

    public function mapOptions(ParameterDefinition $parameterDefinition): array
    {
        $options = $parameterDefinition->getOption('options');

        return [
            'multiple' => $parameterDefinition->getOption('multiple'),
            'expanded' => $parameterDefinition->getOption('expanded'),
            'choices' => is_callable($options) ? $options() : $options,
        ];
    }
}
