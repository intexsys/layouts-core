<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Validator\Structs;

use Netgen\Layouts\API\Values\Collection\Query;
use Netgen\Layouts\API\Values\Collection\QueryUpdateStruct;
use Netgen\Layouts\Tests\Collection\Stubs\QueryType;
use Netgen\Layouts\Tests\TestCase\ValidatorTestCase;
use Netgen\Layouts\Utils\Hydrator;
use Netgen\Layouts\Validator\Constraint\Structs\QueryUpdateStruct as QueryUpdateStructConstraint;
use Netgen\Layouts\Validator\Structs\QueryUpdateStructValidator;
use stdClass;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class QueryUpdateStructValidatorTest extends ValidatorTestCase
{
    public function setUp(): void
    {
        $this->constraint = new QueryUpdateStructConstraint();

        $this->constraint->payload = Query::fromArray(
            [
                'queryType' => new QueryType('query_type'),
            ]
        );

        parent::setUp();
    }

    /**
     * @covers \Netgen\Layouts\Validator\Structs\QueryUpdateStructValidator::validate
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $value, bool $isValid): void
    {
        $queryUpdateStruct = new QueryUpdateStruct();
        (new Hydrator())->hydrate($value, $queryUpdateStruct);

        $this->assertValid($isValid, $queryUpdateStruct);
    }

    /**
     * @covers \Netgen\Layouts\Validator\Structs\QueryUpdateStructValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\\Layouts\\Validator\\Constraint\\Structs\\QueryUpdateStruct", "Symfony\\Component\\Validator\\Constraints\\NotBlank" given');

        $this->constraint = new NotBlank();
        $this->assertValid(true, new QueryUpdateStruct());
    }

    /**
     * @covers \Netgen\Layouts\Validator\Structs\QueryUpdateStructValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidBlock(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\\Layouts\\API\\Values\\Collection\\Query", "stdClass" given');

        $this->constraint->payload = new stdClass();
        $this->assertValid(true, new QueryUpdateStruct());
    }

    /**
     * @covers \Netgen\Layouts\Validator\Structs\QueryUpdateStructValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\\Layouts\\API\\Values\\Collection\\QueryUpdateStruct", "integer" given');

        $this->constraint->payload = new Query();
        $this->assertValid(true, 42);
    }

    public function validateDataProvider(): array
    {
        return [
            [
                [
                    'parameterValues' => [
                        'param' => 'value',
                    ],
                ],
                false,
            ],
            [
                [
                    'locale' => null,
                    'parameterValues' => [
                        'param' => 'value',
                    ],
                ],
                false,
            ],
            [
                [
                    'locale' => '',
                    'parameterValues' => [
                        'param' => 'value',
                    ],
                ],
                false,
            ],
            [
                [
                    'locale' => 'nonexistent',
                    'parameterValues' => [
                        'param' => 'value',
                    ],
                ],
                false,
            ],
            [
                [
                    'locale' => 'en',
                    'parameterValues' => [
                        'param' => 'value',
                    ],
                ],
                true,
            ],
            [
                [
                    'locale' => 'en-US',
                    'parameterValues' => [
                        'param' => 'value',
                    ],
                ],
                false,
            ],
            [
                [
                    'locale' => 'en_US.utf8',
                    'parameterValues' => [
                        'param' => 'value',
                    ],
                ],
                false,
            ],
            [
                [
                    'locale' => 'en',
                    'parameterValues' => [
                        'param' => '',
                    ],
                ],
                false,
            ],
            [
                [
                    'locale' => 'en',
                    'parameterValues' => [
                        'param' => null,
                    ],
                ],
                false,
            ],
            [
                [
                    'locale' => 'en',
                    'parameterValues' => [],
                ],
                true,
            ],
        ];
    }

    protected function getValidator(): ConstraintValidatorInterface
    {
        return new QueryUpdateStructValidator();
    }
}
