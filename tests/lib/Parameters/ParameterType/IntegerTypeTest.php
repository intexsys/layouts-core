<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Parameters\ParameterType;

use Netgen\Layouts\Parameters\ParameterType\IntegerType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validation;

final class IntegerTypeTest extends TestCase
{
    use ParameterTypeTestTrait;

    protected function setUp(): void
    {
        $this->type = new IntegerType();
    }

    /**
     * @covers \Netgen\Layouts\Parameters\ParameterType\IntegerType::getIdentifier
     */
    public function testGetIdentifier(): void
    {
        self::assertSame('integer', $this->type::getIdentifier());
    }

    /**
     * @covers \Netgen\Layouts\Parameters\ParameterType\IntegerType::configureOptions
     *
     * @param array<string, mixed> $options
     * @param bool $required
     * @param mixed $defaultValue
     * @param mixed $expected
     *
     * @dataProvider defaultValueProvider
     */
    public function testGetDefaultValue(array $options, bool $required, $defaultValue, $expected): void
    {
        $parameter = $this->getParameterDefinition($options, $required, $defaultValue);
        self::assertSame($expected, $parameter->getDefaultValue());
    }

    /**
     * @covers \Netgen\Layouts\Parameters\ParameterType\IntegerType::configureOptions
     * @dataProvider validOptionsProvider
     */
    public function testValidOptions(array $options, array $resolvedOptions): void
    {
        $parameter = $this->getParameterDefinition($options);
        self::assertSame($resolvedOptions, $parameter->getOptions());
    }

    /**
     * @covers \Netgen\Layouts\Parameters\ParameterType\IntegerType::configureOptions
     * @dataProvider invalidOptionsProvider
     */
    public function testInvalidOptions(array $options): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getParameterDefinition($options);
    }

    public function defaultValueProvider(): array
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

    public function validOptionsProvider(): array
    {
        return [
            [
                [
                ],
                [
                    'min' => null,
                    'max' => null,
                ],
            ],
            [
                [
                    'max' => 5,
                ],
                [
                    'min' => null,
                    'max' => 5,
                ],
            ],
            [
                [
                    'max' => null,
                ],
                [
                    'min' => null,
                    'max' => null,
                ],
            ],
            [
                [
                    'min' => 5,
                ],
                [
                    'min' => 5,
                    'max' => null,
                ],
            ],
            [
                [
                    'min' => null,
                ],
                [
                    'min' => null,
                    'max' => null,
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
                ],
            ],
        ];
    }

    public function invalidOptionsProvider(): array
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
                    'undefined_value' => 'Value',
                ],
            ],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $required
     * @param bool $isValid
     *
     * @covers \Netgen\Layouts\Parameters\ParameterType\IntegerType::getValueConstraints
     * @dataProvider validationProvider
     */
    public function testValidation($value, bool $required, bool $isValid): void
    {
        $parameter = $this->getParameterDefinition(['min' => 5, 'max' => 10], $required);
        $validator = Validation::createValidator();

        $errors = $validator->validate($value, $this->type->getConstraints($parameter, $value));
        self::assertSame($isValid, $errors->count() === 0);
    }

    public function validationProvider(): array
    {
        return [
            ['12', false, false],
            [12.3, false, false],
            [true, false, false],
            [[], false, false],
            [12, false, false],
            [0, false, false],
            [-12, false, false],
            [5, false, true],
            [7, false, true],
            [10, false, true],
            [null, false, true],
            [5, true, true],
            [7, true, true],
            [10, true, true],
            [null, true, false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $isEmpty
     *
     * @covers \Netgen\Layouts\Parameters\ParameterType\IntegerType::isValueEmpty
     * @dataProvider emptyProvider
     */
    public function testIsValueEmpty($value, bool $isEmpty): void
    {
        self::assertSame($isEmpty, $this->type->isValueEmpty($this->getParameterDefinition(), $value));
    }

    public function emptyProvider(): array
    {
        return [
            [null, true],
            [42, false],
            [0, false],
        ];
    }
}
