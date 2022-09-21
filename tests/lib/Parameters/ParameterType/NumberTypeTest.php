<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Parameters\ParameterType;

use Netgen\Layouts\Parameters\ParameterType\NumberType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validation;

final class NumberTypeTest extends TestCase
{
    use ParameterTypeTestTrait;

    protected function setUp(): void
    {
        $this->type = new NumberType();
    }

    /**
     * @covers \Netgen\Layouts\Parameters\ParameterType\NumberType::getIdentifier
     */
    public function testGetIdentifier(): void
    {
        self::assertSame('number', $this->type::getIdentifier());
    }

    /**
     * @covers \Netgen\Layouts\Parameters\ParameterType\NumberType::configureOptions
     *
     * @param array<string, mixed> $options
     * @param mixed $defaultValue
     * @param mixed $expected
     *
     * @dataProvider defaultValueDataProvider
     */
    public function testGetDefaultValue(array $options, bool $required, $defaultValue, $expected): void
    {
        $parameter = $this->getParameterDefinition($options, $required, $defaultValue);
        self::assertSame($expected, $parameter->getDefaultValue());
    }

    /**
     * @param mixed[] $options
     * @param mixed[] $resolvedOptions
     *
     * @covers \Netgen\Layouts\Parameters\ParameterType\NumberType::configureOptions
     *
     * @dataProvider validOptionsDataProvider
     */
    public function testValidOptions(array $options, array $resolvedOptions): void
    {
        $parameter = $this->getParameterDefinition($options);
        self::assertSame($resolvedOptions, $parameter->getOptions());
    }

    /**
     * @param mixed[] $options
     *
     * @covers \Netgen\Layouts\Parameters\ParameterType\NumberType::configureOptions
     *
     * @dataProvider invalidOptionsDataProvider
     */
    public function testInvalidOptions(array $options): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getParameterDefinition($options);
    }

    public function defaultValueDataProvider(): array
    {
        return [
            [[], true, null, null],
            [['min' => 3], true, null, 3],
            [[], false, null, null],
            [['min' => 3], false, null, null],
            [[], true, 4, 4],
            [['min' => 3], true, 4, 4],
            [[], false, 4, 4],
            [['min' => 3], false, 4, 4],
        ];
    }

    public function validOptionsDataProvider(): array
    {
        return [
            [
                [
                ],
                [
                    'min' => null,
                    'max' => null,
                    'scale' => 3,
                ],
            ],
            [
                [
                    'max' => 5,
                ],
                [
                    'min' => null,
                    'max' => 5,
                    'scale' => 3,
                ],
            ],
            [
                [
                    'max' => null,
                ],
                [
                    'min' => null,
                    'max' => null,
                    'scale' => 3,
                ],
            ],
            [
                [
                    'min' => 5,
                ],
                [
                    'min' => 5,
                    'max' => null,
                    'scale' => 3,
                ],
            ],
            [
                [
                    'min' => null,
                ],
                [
                    'min' => null,
                    'max' => null,
                    'scale' => 3,
                ],
            ],
            [
                [
                    'min' => 5,
                    'max' => 10,
                ],
                [
                    'min' => 5,
                    'max' => 10,
                    'scale' => 3,
                ],
            ],
            [
                [
                    'min' => 5,
                    'max' => 3,
                ],
                [
                    'min' => 5,
                    'max' => 5,
                    'scale' => 3,
                ],
            ],
            [
                [
                    'scale' => 5,
                ],
                [
                    'min' => null,
                    'max' => null,
                    'scale' => 5,
                ],
            ],
        ];
    }

    public function invalidOptionsDataProvider(): array
    {
        return [
            [
                [
                    'max' => [],
                ],
                [
                    'max' => 5.5,
                ],
                [
                    'max' => '5',
                ],
                [
                    'min' => [],
                ],
                [
                    'min' => 5.5,
                ],
                [
                    'min' => '5',
                ],
                [
                    'min' => [],
                ],
                [
                    'min' => 5.5,
                ],
                [
                    'min' => '5',
                ],
                [
                    'undefined_value' => 'Value',
                ],
            ],
        ];
    }

    /**
     * @param mixed $value
     *
     * @covers \Netgen\Layouts\Parameters\ParameterType\NumberType::getValueConstraints
     *
     * @dataProvider validationDataProvider
     */
    public function testValidation($value, bool $required, bool $isValid): void
    {
        $parameter = $this->getParameterDefinition(['min' => 5, 'max' => 10], $required);
        $validator = Validation::createValidator();

        $errors = $validator->validate($value, $this->type->getConstraints($parameter, $value));
        self::assertSame($isValid, $errors->count() === 0);
    }

    public function validationDataProvider(): array
    {
        return [
            ['12', false, false],
            [true, false, false],
            [[], false, false],
            [12, false, false],
            [12.3, false, false],
            [0, false, false],
            [-12, false, false],
            [5, false, true],
            [7, false, true],
            [7.5, false, true],
            [10, false, true],
            [null, false, true],
            [5, true, true],
            [7, true, true],
            [7.5, true, true],
            [10, true, true],
            [null, true, false],
        ];
    }

    /**
     * @param mixed $value
     *
     * @covers \Netgen\Layouts\Parameters\ParameterType\NumberType::isValueEmpty
     *
     * @dataProvider emptyDataProvider
     */
    public function testIsValueEmpty($value, bool $isEmpty): void
    {
        self::assertSame($isEmpty, $this->type->isValueEmpty($this->getParameterDefinition(), $value));
    }

    public function emptyDataProvider(): array
    {
        return [
            [null, true],
            [42, false],
            [42.5, false],
            [0, false],
        ];
    }
}
