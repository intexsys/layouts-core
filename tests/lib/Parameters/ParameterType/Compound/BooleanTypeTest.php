<?php

namespace Netgen\BlockManager\Tests\Parameters\ParameterType\Compound;

use Netgen\BlockManager\Parameters\ParameterType\Compound\BooleanType;
use Netgen\BlockManager\Tests\Parameters\ParameterType\ParameterTypeTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class BooleanTypeTest extends TestCase
{
    use ParameterTypeTestTrait;

    public function setUp()
    {
        $this->type = new BooleanType();
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterType\Compound\BooleanType::getIdentifier
     */
    public function testGetIdentifier()
    {
        $this->assertEquals('compound_boolean', $this->type->getIdentifier());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterType\Compound\BooleanType::configureOptions
     *
     * @param array $options
     * @param bool $required
     * @param mixed $defaultValue
     * @param mixed $expected
     *
     * @dataProvider defaultValueProvider
     */
    public function testGetDefaultValue(array $options, $required, $defaultValue, $expected)
    {
        $parameter = $this->getParameterDefinition($options, $required, $defaultValue);
        $this->assertEquals($expected, $parameter->getDefaultValue());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterType\Compound\BooleanType::configureOptions
     * @dataProvider validOptionsProvider
     *
     * @param array $options
     * @param array $resolvedOptions
     */
    public function testValidOptions($options, $resolvedOptions)
    {
        $parameter = $this->getParameterDefinition($options);
        $this->assertEquals($resolvedOptions, $parameter->getOptions());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterType\Compound\BooleanType::configureOptions
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidArgumentException
     * @dataProvider invalidOptionsProvider
     *
     * @param array $options
     */
    public function testInvalidOptions($options)
    {
        $this->getParameterDefinition($options);
    }

    /**
     * Provider for testing default parameter values.
     *
     * @return array
     */
    public function defaultValueProvider()
    {
        return array(
            array(array(), true, null, false),
            array(array(), false, null, null),
            array(array(), true, false, false),
            array(array(), false, false, false),
            array(array(), true, true, true),
            array(array(), false, true, true),
        );
    }

    /**
     * Provider for testing valid parameter attributes.
     *
     * @return array
     */
    public function validOptionsProvider()
    {
        return array(
            array(
                array(
                    'reverse' => false,
                ),
                array(
                    'reverse' => false,
                ),
            ),
            array(
                array(
                    'reverse' => true,
                ),
                array(
                    'reverse' => true,
                ),
            ),
            array(
                array(),
                array(
                    'reverse' => false,
                ),
            ),
        );
    }

    /**
     * Provider for testing invalid parameter attributes.
     *
     * @return array
     */
    public function invalidOptionsProvider()
    {
        return array(
            array(
                array(
                    'reverse' => 'true',
                ),
                array(
                    'undefined_value' => 'Value',
                ),
            ),
        );
    }

    /**
     * @param mixed $value
     * @param bool $required
     * @param bool $isValid
     *
     * @covers \Netgen\BlockManager\Parameters\ParameterType\Compound\BooleanType::getRequiredConstraints
     * @covers \Netgen\BlockManager\Parameters\ParameterType\Compound\BooleanType::getValueConstraints
     * @dataProvider validationProvider
     */
    public function testValidation($value, $required, $isValid)
    {
        $parameter = $this->getParameterDefinition(array(), $required);
        $validator = Validation::createValidator();

        $errors = $validator->validate($value, $this->type->getConstraints($parameter, $value));
        $this->assertEquals($isValid, $errors->count() === 0);
    }

    /**
     * Provider for testing valid parameter values.
     *
     * @return array
     */
    public function validationProvider()
    {
        return array(
            array('12', false, false),
            array(12.3, false, false),
            array(true, false, true),
            array(false, false, true),
            array(null, false, true),
            array(true, true, true),
            array(false, true, true),
            array(null, true, false),
            array(array(), false, false),
            array(12, false, false),
        );
    }

    /**
     * @param mixed $value
     * @param bool $isEmpty
     *
     * @covers \Netgen\BlockManager\Parameters\ParameterType\Compound\BooleanType::isValueEmpty
     * @dataProvider emptyProvider
     */
    public function testIsValueEmpty($value, $isEmpty)
    {
        $this->assertEquals($isEmpty, $this->type->isValueEmpty($this->getParameterDefinition(), $value));
    }

    /**
     * Provider for testing if the value is empty.
     *
     * @return array
     */
    public function emptyProvider()
    {
        return array(
            array(null, true),
            array(false, false),
            array(true, false),
        );
    }
}
