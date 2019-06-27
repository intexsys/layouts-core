<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Validator;

use Netgen\Layouts\Exception\Validation\ValidationException;
use Netgen\Layouts\Tests\TestCase\ValidatorFactory;
use Netgen\Layouts\Tests\Validator\Stubs\ValueValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ValidatorTraitTest extends TestCase
{
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private $baseValidator;

    /**
     * @var \Netgen\Layouts\Tests\Validator\Stubs\ValueValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->baseValidator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new ValidatorFactory($this))
            ->getValidator();

        $this->validator = new ValueValidator();
        $this->validator->setValidator($this->baseValidator);
    }

    /**
     * @param mixed $identifier
     * @param bool $isValid
     *
     * @covers \Netgen\Layouts\Validator\ValidatorTrait::validate
     * @covers \Netgen\Layouts\Validator\ValidatorTrait::validateIdentifier
     * @dataProvider validateIdentifierDataProvider
     */
    public function testValidateIdentifier($identifier, bool $isValid): void
    {
        if (!$isValid) {
            $this->expectException(ValidationException::class);
        }

        // Tests without assertions are not covered by PHPUnit, so we fake the assertion count
        $this->addToAssertionCount(1);

        $this->validator->validateIdentifier($identifier);
    }

    /**
     * @param mixed $position
     * @param bool $isRequired
     * @param bool $isValid
     *
     * @covers \Netgen\Layouts\Validator\ValidatorTrait::validate
     * @covers \Netgen\Layouts\Validator\ValidatorTrait::validatePosition
     * @dataProvider validatePositionDataProvider
     */
    public function testValidatePosition($position, bool $isRequired, bool $isValid): void
    {
        if (!$isValid) {
            $this->expectException(ValidationException::class);
        }

        // Tests without assertions are not covered by PHPUnit, so we fake the assertion count
        $this->addToAssertionCount(1);

        $this->validator->validatePosition($position, null, $isRequired);
    }

    /**
     * @covers \Netgen\Layouts\Validator\ValidatorTrait::validate
     * @covers \Netgen\Layouts\Validator\ValidatorTrait::validatePosition
     */
    public function testValidatePositionWithDefaultRequiredValue(): void
    {
        $this->validator->validatePosition(null);

        // Tests without assertions are not covered by PHPUnit, so we fake the assertion count
        $this->addToAssertionCount(1);
    }

    /**
     * @covers \Netgen\Layouts\Validator\ValidatorTrait::validate
     * @covers \Netgen\Layouts\Validator\ValidatorTrait::validateLocale
     * @dataProvider validateLocaleDataProvider
     */
    public function testValidateLocale(string $locale, bool $isValid): void
    {
        if (!$isValid) {
            $this->expectException(ValidationException::class);
        }

        // Tests without assertions are not covered by PHPUnit, so we fake the assertion count
        $this->addToAssertionCount(1);

        $this->validator->validateLocale($locale);
    }

    public function validateIdDataProvider(): array
    {
        return [
            [24, true],
            ['24', true],
            ['', false],
            [[], false],
            [null, false],
        ];
    }

    public function validateIdentifierDataProvider(): array
    {
        return [
            ['a', true],
            ['identifier', true],
            ['identifier_2', true],
            ['345identifier', true],
            ['345_identifier', true],
            ['other identifier', false],
            ['345', false],
            ['345_678', false],
            ['___', false],
            ['', false],
        ];
    }

    public function validatePositionDataProvider(): array
    {
        return [
            [-5, false, false],
            [-5, true, false],
            [-1, false, false],
            [-1, true, false],
            [0, false, true],
            [0, true, true],
            [24, false, true],
            [24, true, true],
            [null, false, true],
            [null, true, false],
        ];
    }

    public function validateOffsetAndLimitDataProvider(): array
    {
        return [
            [0, null, true],
            [5, null, true],
            [null, null, false],
            [0, 1, true],
            [5, 1, true],
            [null, 1, false],
        ];
    }

    public function validateLocaleDataProvider(): array
    {
        return [
            ['en', true],
            ['en_US', true],
            ['pt', true],
            ['pt_PT', true],
            ['zh_Hans', true],
            ['fil_PH', true],
            // We do not allow non-canonicalized locales
            ['en-US', false],
            ['es-AR', false],
            ['fr_FR.utf8', false],
            ['EN', false],
            // Invalid locales
            ['foobar', false],
        ];
    }
}
