<?php

namespace Netgen\BlockManager\Parameters\Parameter\Compound;

use Netgen\BlockManager\Parameters\CompoundParameter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class Boolean extends CompoundParameter
{
    /**
     * Returns the parameter type.
     *
     * @return string
     */
    public function getType()
    {
        return 'compound_boolean';
    }

    /**
     * Returns the default parameter value.
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        if ($this->isRequired && $this->defaultValue === null) {
            return false;
        }

        return parent::getDefaultValue();
    }

    /**
     * Configures the options for this parameter.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $optionsResolver
     */
    protected function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setDefault('reverse', false);
        $optionsResolver->setRequired(array('reverse'));
        $optionsResolver->setAllowedTypes('reverse', 'bool');
    }

    /**
     * Returns constraints that will be used when parameter is required.
     *
     * Overriden because base NotBlank constraint checks for `false` too.
     *
     * @param mixed $value
     *
     * @return \Symfony\Component\Validator\Constraint[]
     */
    public function getRequiredConstraints($value)
    {
        if ($this->isRequired()) {
            return array(
                new Constraints\NotNull(),
            );
        }

        return array();
    }

    /**
     * Returns constraints that will be used to validate the parameter value.
     *
     * @param mixed $value
     *
     * @return \Symfony\Component\Validator\Constraint[]
     */
    public function getValueConstraints($value)
    {
        return array(
            new Constraints\Type(
                array(
                    'type' => 'bool',
                )
            ),
        );
    }
}
