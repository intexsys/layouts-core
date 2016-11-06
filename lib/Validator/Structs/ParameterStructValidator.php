<?php

namespace Netgen\BlockManager\Validator\Structs;

use Netgen\BlockManager\Parameters\CompoundParameterInterface;
use Netgen\BlockManager\API\Values\ParameterStruct;
use Netgen\BlockManager\Parameters\Registry\ParameterFilterRegistryInterface;
use Netgen\BlockManager\Validator\Constraint\Structs\ParameterStruct as ParameterStructConstraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ParameterStructValidator extends ConstraintValidator
{
    /**
     * @var \Netgen\BlockManager\Parameters\Registry\ParameterFilterRegistryInterface
     */
    protected $parameterFilterRegistry;

    /**
     * Constructor.
     *
     * @param \Netgen\BlockManager\Parameters\Registry\ParameterFilterRegistryInterface $parameterFilterRegistry
     */
    public function __construct(ParameterFilterRegistryInterface $parameterFilterRegistry)
    {
        $this->parameterFilterRegistry = $parameterFilterRegistry;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param \Symfony\Component\Validator\Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ParameterStructConstraint) {
            throw new UnexpectedTypeException($constraint, ParameterStructConstraint::class);
        }

        if (!$value instanceof ParameterStruct) {
            throw new UnexpectedTypeException($value, ParameterStruct::class);
        }

        $this->filterParameters($value, $constraint->parameters);

        /** @var \Symfony\Component\Validator\Validator\ContextualValidatorInterface $validator */
        $validator = $this->context->getValidator()->inContext($this->context);

        $validator->validate(
            $value->getParameters(),
            new Constraints\Collection(
                array(
                    'fields' => $this->buildConstraintFields($value, $constraint),
                )
            )
        );
    }

    /**
     * Filters the parameter values.
     *
     * @param \Netgen\BlockManager\API\Values\ParameterStruct $parameterStruct
     * @param \Netgen\BlockManager\Parameters\ParameterInterface[] $parameters
     */
    protected function filterParameters(ParameterStruct $parameterStruct, array $parameters)
    {
        foreach ($parameterStruct->getParameters() as $parameterName => $parameter) {
            if (!isset($parameters[$parameterName])) {
                continue;
            }

            $filters = $this->parameterFilterRegistry->getParameterFilters($parameters[$parameterName]->getType()->getIdentifier());
            foreach ($filters as $filter) {
                $parameter = $filter->filter($parameter);
            }

            $parameterStruct->setParameter($parameterName, $parameter);
        }
    }

    /**
     * Builds the "fields" array from provided parameters and parameter values.
     *
     * @param \Netgen\BlockManager\API\Values\ParameterStruct $parameterStruct
     * @param \Netgen\BlockManager\Validator\Constraint\Structs\ParameterStruct $constraint
     *
     * @return array
     */
    protected function buildConstraintFields(ParameterStruct $parameterStruct, ParameterStructConstraint $constraint)
    {
        $fields = array();
        foreach ($constraint->parameters as $parameter) {
            $parameterName = $parameter->getName();
            $parameterValue = $parameterStruct->hasParameter($parameterName) ?
                $parameterStruct->getParameter($parameterName) :
                null;

            $constraints = $parameter->getType()->getConstraints($parameter, $parameterValue);

            $fields[$parameterName] = !$constraint->allowMissingFields && $parameter->isRequired() ?
                new Constraints\Required($constraints) :
                new Constraints\Optional($constraints);

            if ($parameter instanceof CompoundParameterInterface) {
                foreach ($parameter->getParameters() as $subParameter) {
                    $subParameterName = $subParameter->getName();
                    $subParameterValue = $parameterStruct->hasParameter($subParameterName) ?
                        $parameterStruct->getParameter($subParameterName) :
                        null;

                    $constraints = array();
                    if ($subParameterValue !== null) {
                        $constraints = $subParameter->getType()->getConstraints($subParameter, $subParameterValue);
                    }

                    $fields[$subParameterName] = new Constraints\Optional($constraints);
                }
            }
        }

        return $fields;
    }
}
