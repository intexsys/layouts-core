<?php

namespace Netgen\BlockManager\Validator;

use Netgen\BlockManager\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ValidatorTrait
{
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validates the value against a set of provided constraints.
     *
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint|\Symfony\Component\Validator\Constraint[] $constraints
     * @param string $propertyPath
     *
     * @throws \Netgen\BlockManager\Exception\InvalidArgumentException If the validation failed
     */
    public function validate($value, $constraints, $propertyPath = null)
    {
        $violations = $this->validator->validate($value, $constraints);

        if (count($violations) > 0) {
            throw new InvalidArgumentException(
                $propertyPath !== null ? $propertyPath : $violations[0]->getPropertyPath(),
                $violations[0]->getMessage()
            );
        }
    }
}
