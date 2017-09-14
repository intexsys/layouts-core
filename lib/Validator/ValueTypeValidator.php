<?php

namespace Netgen\BlockManager\Validator;

use Netgen\BlockManager\Item\Registry\ValueTypeRegistryInterface;
use Netgen\BlockManager\Validator\Constraint\ValueType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates if the provided value type exists in the system.
 */
class ValueTypeValidator extends ConstraintValidator
{
    /**
     * @var \Netgen\BlockManager\Item\Registry\ValueTypeRegistryInterface
     */
    protected $valueTypeRegistry;

    public function __construct(ValueTypeRegistryInterface $valueTypeRegistry)
    {
        $this->valueTypeRegistry = $valueTypeRegistry;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValueType) {
            throw new UnexpectedTypeException($constraint, ValueType::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!$this->valueTypeRegistry->hasValueType($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%valueType%', $value)
                ->addViolation();
        }
    }
}
