<?php

declare(strict_types=1);

namespace Netgen\Layouts\Core\Validator;

use Netgen\Layouts\Validator\Constraint\Locale as LocaleConstraint;
use Netgen\Layouts\Validator\ValidatorTrait;
use Symfony\Component\Validator\Constraints;

abstract class Validator
{
    use ValidatorTrait;

    /**
     * Validates the provided identifier to be a string.
     *
     * Use the $propertyPath to change the name of the validated property in the error message.
     *
     * @throws \Netgen\Layouts\Exception\Validation\ValidationException If the validation failed
     */
    public function validateIdentifier(string $identifier, ?string $propertyPath = null): void
    {
        $constraints = [
            new Constraints\NotBlank(),
            new Constraints\Regex(
                [
                    'pattern' => '/^[A-Za-z0-9_]*[A-Za-z][A-Za-z0-9_]*$/',
                ]
            ),
        ];

        $this->validate($identifier, $constraints, $propertyPath);
    }

    /**
     * Validates the provided position to be an integer greater than or equal to 0.
     *
     * If $isRequired is set to false, null value is also allowed.
     *
     * Use the $propertyPath to change the name of the validated property in the error message.
     *
     * @throws \Netgen\Layouts\Exception\Validation\ValidationException If the validation failed
     */
    public function validatePosition(?int $position, ?string $propertyPath = null, bool $isRequired = false): void
    {
        if (!$isRequired && $position === null) {
            return;
        }

        $constraints = [
            new Constraints\NotBlank(),
            new Constraints\GreaterThanOrEqual(0),
        ];

        $this->validate($position, $constraints, $propertyPath);
    }

    /**
     * Validates the provided locale.
     *
     * @throws \Netgen\Layouts\Exception\Validation\ValidationException If the validation failed
     */
    public function validateLocale(string $locale, ?string $propertyPath = null): void
    {
        $this->validate(
            $locale,
            [
                new Constraints\NotBlank(),
                new LocaleConstraint(),
            ],
            $propertyPath
        );
    }
}
