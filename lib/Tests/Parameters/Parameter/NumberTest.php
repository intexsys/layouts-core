<?php

namespace Netgen\BlockManager\Tests\Parameters\Parameter;

use Netgen\BlockManager\Parameters\Parameter\Number;
use Symfony\Component\Validator\Validation;
use PHPUnit\Framework\TestCase;

class NumberTest extends TestCase
{
    /**
     * @covers \Netgen\BlockManager\Parameters\Parameter\Number::getType
     */
    public function testGetType()
    {
        $parameter = $this->getParameter();
        $this->assertEquals('number', $parameter->getType());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\Parameter\Number::getDefaultValue
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
        $parameter = $this->getParameter($options, $required, $defaultValue);
        $this->assertEquals($expected, $parameter->getDefaultValue());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\Parameter\Number::getOptions
     * @covers \Netgen\BlockManager\Parameters\Parameter\Number::configureOptions
     * @dataProvider validOptionsProvider
     *
     * @param array $options
     * @param array $resolvedOptions
     */
    public function testValidOptions($options, $resolvedOptions)
    {
        $parameter = $this->getParameter($options);
        $this->assertEquals($resolvedOptions, $parameter->getOptions());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\Parameter\Number::getOptions
     * @covers \Netgen\BlockManager\Parameters\Parameter\Number::configureOptions
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidArgumentException
     * @dataProvider invalidOptionsProvider
     *
     * @param array $options
     */
    public function testInvalidOptions($options)
    {
        $this->getParameter($options);
    }

    /**
     * Returns the parameter under test.
     *
     * @param array $options
     * @param bool $required
     * @param mixed $defaultValue
     *
     * @return \Netgen\BlockManager\Parameters\Parameter\Number
     */
    public function getParameter(array $options = array(), $required = false, $defaultValue = null)
    {
        return new Number($options, $required, $defaultValue);
    }

    /**
     * Provider for testing default parameter values.
     *
     * @return array
     */
    public function defaultValueProvider()
    {
        return array(
            array(array(), true, null, null),
            array(array('min' => 3), true, null, 3),
            array(array(), false, null, null),
            array(array('min' => 3), false, null, null),
            array(array(), true, 4, 4),
            array(array('min' => 3), true, 4, 4),
            array(array(), false, 4, 4),
            array(array('min' => 3), false, 4, 4),
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
                ),
                array(
                    'max' => null,
                    'min' => null,
                    'scale' => 3,
                ),
            ),
            array(
                array(
                    'max' => 5,
                ),
                array(
                    'max' => 5,
                    'min' => null,
                    'scale' => 3,
                ),
            ),
            array(
                array(
                    'max' => null,
                ),
                array(
                    'max' => null,
                    'min' => null,
                    'scale' => 3,
                ),
            ),
            array(
                array(
                    'min' => 5,
                ),
                array(
                    'min' => 5,
                    'max' => null,
                    'scale' => 3,
                ),
            ),
            array(
                array(
                    'min' => null,
                ),
                array(
                    'max' => null,
                    'min' => null,
                    'scale' => 3,
                ),
            ),
            array(
                array(
                    'min' => 5,
                    'max' => 10,
                ),
                array(
                    'min' => 5,
                    'max' => 10,
                    'scale' => 3,
                ),
            ),
            array(
                array(
                    'min' => 5,
                    'max' => 3,
                ),
                array(
                    'min' => 5,
                    'max' => 5,
                    'scale' => 3,
                ),
            ),
            array(
                array(
                    'scale' => 5,
                ),
                array(
                    'min' => null,
                    'max' => null,
                    'scale' => 5,
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
                    'max' => array(),
                ),
                array(
                    'max' => 5.5,
                ),
                array(
                    'max' => '5',
                ),
                array(
                    'min' => array(),
                ),
                array(
                    'min' => 5.5,
                ),
                array(
                    'min' => '5',
                ),
                array(
                    'min' => array(),
                ),
                array(
                    'min' => 5.5,
                ),
                array(
                    'min' => '5',
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
     * @covers \Netgen\BlockManager\Parameters\Parameter\Number::getValueConstraints
     * @dataProvider validationProvider
     */
    public function testValidation($value, $required, $isValid)
    {
        $parameter = $this->getParameter(array('min' => 5, 'max' => 10), $required);
        $validator = Validation::createValidator();

        $errors = $validator->validate($value, $parameter->getConstraints());
        $this->assertEquals($isValid, $errors->count() == 0);
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
            array(true, false, false),
            array(array(), false, false),
            array(12, false, false),
            array(12.3, false, false),
            array(0, false, false),
            array(-12, false, false),
            array(5, false, true),
            array(7, false, true),
            array(7.5, false, true),
            array(10, false, true),
            array(null, false, true),
            array(5, true, true),
            array(7, true, true),
            array(7.5, true, true),
            array(10, true, true),
            array(null, true, false),
        );
    }
}
