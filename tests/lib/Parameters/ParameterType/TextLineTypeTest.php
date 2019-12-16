<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Parameters\ParameterType;

use Netgen\Layouts\Parameters\ParameterType\TextLineType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validation;

final class TextLineTypeTest extends TestCase
{
    use ParameterTypeTestTrait;

    protected function setUp(): void
    {
        $this->type = new TextLineType();
    }

    /**
     * @covers \Netgen\Layouts\Parameters\ParameterType\TextLineType::getIdentifier
     */
    public function testGetIdentifier(): void
    {
        self::assertSame('text_line', $this->type::getIdentifier());
    }

    /**
     * @param mixed[] $options
     * @param mixed[] $resolvedOptions
     *
     * @covers \Netgen\Layouts\Parameters\ParameterType\TextLineType::configureOptions
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
     * @covers \Netgen\Layouts\Parameters\ParameterType\TextLineType::configureOptions
     * @dataProvider invalidOptionsDataProvider
     */
    public function testInvalidOptions(array $options): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getParameterDefinition($options);
    }

    public function validOptionsDataProvider(): array
    {
        return [
            [
                [],
                [],
            ],
        ];
    }

    public function invalidOptionsDataProvider(): array
    {
        return [
            [
                [
                    'undefined_value' => 'Value',
                ],
            ],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $isValid
     *
     * @covers \Netgen\Layouts\Parameters\ParameterType\TextLineType::getRequiredConstraints
     * @covers \Netgen\Layouts\Parameters\ParameterType\TextLineType::getValueConstraints
     * @dataProvider validationDataProvider
     */
    public function testValidation($value, bool $isValid): void
    {
        $parameter = $this->getParameterDefinition();
        $validator = Validation::createValidator();

        $errors = $validator->validate($value, $this->type->getConstraints($parameter, $value));
        self::assertSame($isValid, $errors->count() === 0);
    }

    public function validationDataProvider(): array
    {
        return [
            ['test', true],
            [null, true],
            [12.3, false],
            [12, false],
            [true, false],
            [false, false],
            [[], false],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $isEmpty
     *
     * @covers \Netgen\Layouts\Parameters\ParameterType\TextLineType::isValueEmpty
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
            ['foo', false],
            ['', true],
        ];
    }
}
