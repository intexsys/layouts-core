<?php

namespace Netgen\BlockManager\Parameters\ParameterType;

use Netgen\BlockManager\Parameters\ParameterInterface;
use Netgen\BlockManager\Parameters\ParameterType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

/**
 * Parameter type used to store and validate a selection option.
 *
 * It can have a single value (string) or a multiple value (array of strings).
 */
class ChoiceType extends ParameterType
{
    public function getIdentifier()
    {
        return 'choice';
    }

    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefault('multiple', false);
        $optionsResolver->setRequired(array('multiple', 'options'));
        $optionsResolver->setAllowedTypes('multiple', 'bool');
        $optionsResolver->setAllowedTypes('options', array('array', 'callable'));

        $optionsResolver->setAllowedValues(
            'options',
            function ($value) {
                if (is_callable($value)) {
                    return true;
                }

                return !empty($value);
            }
        );

        $optionsResolver->setDefault(
            'default_value',
            function (Options $options, $previousValue) {
                if ($options['required']) {
                    if (!is_callable($options['options']) && !empty($options['options'])) {
                        $defaultValue = array_values($options['options'])[0];

                        return $options['multiple'] ? array($defaultValue) : $defaultValue;
                    }
                }

                return $previousValue;
            }
        );
    }

    public function fromHash(ParameterInterface $parameter, $value)
    {
        if ($value === null || $value === array()) {
            return null;
        }

        if ($parameter->getOption('multiple')) {
            return is_array($value) ? $value : array($value);
        }

        return is_array($value) ? array_values($value)[0] : $value;
    }

    public function isValueEmpty(ParameterInterface $parameter, $value)
    {
        return $value === null || $value === array();
    }

    protected function getValueConstraints(ParameterInterface $parameter, $value)
    {
        $options = $parameter->getOptions();

        return array(
            new Constraints\Choice(
                array(
                    'choices' => array_values(
                        is_callable($options['options']) ?
                            $options['options']() :
                            $options['options']
                        ),
                    'multiple' => $options['multiple'],
                    'strict' => true,
                )
            ),
        );
    }
}
