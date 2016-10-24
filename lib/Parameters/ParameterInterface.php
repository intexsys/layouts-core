<?php

namespace Netgen\BlockManager\Parameters;

interface ParameterInterface
{
    /**
     * Returns the parameter type.
     *
     * @return string
     */
    public function getType();

    /**
     * Returns the parameter options.
     *
     * @return array
     */
    public function getOptions();

    /**
     * Returns the parameter constraints.
     *
     * @param mixed $value
     *
     * @return \Symfony\Component\Validator\Constraint[]
     */
    public function getConstraints($value);

    /**
     * Returns constraints that will be used when parameter is required.
     *
     * @param mixed $value
     *
     * @return \Symfony\Component\Validator\Constraint[]
     */
    public function getRequiredConstraints($value);

    /**
     * Returns constraints that will be used to validate the parameter value.
     *
     * @param mixed $value
     *
     * @return \Symfony\Component\Validator\Constraint[]
     */
    public function getValueConstraints($value);

    /**
     * Returns if the parameter is required.
     *
     * @return bool
     */
    public function isRequired();

    /**
     * Returns the default parameter value.
     *
     * @return mixed
     */
    public function getDefaultValue();

    /**
     * @return array
     */
    public function getGroups();
}
