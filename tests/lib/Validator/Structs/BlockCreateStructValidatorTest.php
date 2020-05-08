<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Validator\Structs;

use Netgen\Layouts\API\Values\Block\BlockCreateStruct;
use Netgen\Layouts\Block\BlockDefinition;
use Netgen\Layouts\Block\BlockDefinition\Configuration\ItemViewType;
use Netgen\Layouts\Block\BlockDefinition\Configuration\ViewType;
use Netgen\Layouts\Block\BlockDefinitionInterface;
use Netgen\Layouts\Block\ContainerDefinition;
use Netgen\Layouts\Block\ContainerDefinitionInterface;
use Netgen\Layouts\Tests\Block\Stubs\BlockDefinitionHandlerWithRequiredParameter;
use Netgen\Layouts\Tests\Block\Stubs\ContainerDefinitionHandler;
use Netgen\Layouts\Tests\TestCase\ValidatorTestCase;
use Netgen\Layouts\Utils\Hydrator;
use Netgen\Layouts\Validator\Constraint\Structs\BlockCreateStruct as BlockCreateStructConstraint;
use Netgen\Layouts\Validator\Structs\BlockCreateStructValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class BlockCreateStructValidatorTest extends ValidatorTestCase
{
    protected function setUp(): void
    {
        $this->constraint = new BlockCreateStructConstraint();

        parent::setUp();
    }

    /**
     * @param mixed[] $value
     *
     * @covers \Netgen\Layouts\Validator\Structs\BlockCreateStructValidator::validate
     * @dataProvider validateDataProvider
     */
    public function testValidate(array $value, bool $isValid): void
    {
        $blockCreateStruct = new BlockCreateStruct($value['definition']);
        (new Hydrator())->hydrate($value, $blockCreateStruct);

        $this->assertValid($isValid, $blockCreateStruct);
    }

    /**
     * @covers \Netgen\Layouts\Validator\Structs\BlockCreateStructValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\\Layouts\\Validator\\Constraint\\Structs\\BlockCreateStruct", "Symfony\\Component\\Validator\\Constraints\\NotBlank" given');

        $this->constraint = new NotBlank();
        $this->assertValid(true, new BlockCreateStruct(new BlockDefinition()));
    }

    /**
     * @covers \Netgen\Layouts\Validator\Structs\BlockCreateStructValidator::validate
     */
    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\\Layouts\\API\\Values\\Block\\BlockCreateStruct", "integer" given');

        $this->assertValid(true, 42);
    }

    public function validateDataProvider(): array
    {
        return [
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                true,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'nonexistent',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => '',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'nonexistent',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => '',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => null,
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                true,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => '',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                true,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 42,
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => true,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                true,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => null,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => 42,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => null,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => 42,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => '',
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => null,
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_id' => 'id',
                    ],
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => '',
                    ],
                ],
                true,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => null,
                    ],
                ],
                true,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                    ],
                ],
                true,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                ],
                false,
            ],
            [
                [
                    'definition' => $this->getBlockDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [],
                ],
                false,
            ],

            // Container block definitions

            [
                [
                    'definition' => $this->getContainerDefinition(),
                    'viewType' => 'large',
                    'itemViewType' => 'standard',
                    'name' => 'My block',
                    'isTranslatable' => false,
                    'alwaysAvailable' => true,
                    'parameterValues' => [
                        'css_class' => 'class',
                        'css_id' => 'id',
                    ],
                ],
                true,
            ],
        ];
    }

    protected function getValidator(): ConstraintValidatorInterface
    {
        return new BlockCreateStructValidator();
    }

    private function getBlockDefinition(): BlockDefinitionInterface
    {
        $handler = new BlockDefinitionHandlerWithRequiredParameter();

        return BlockDefinition::fromArray(
            [
                'parameterDefinitions' => $handler->getParameterDefinitions(),
                'viewTypes' => [
                    'large' => ViewType::fromArray(
                        [
                            'itemViewTypes' => [
                                'standard' => new ItemViewType(),
                            ],
                        ]
                    ),
                ],
            ]
        );
    }

    private function getContainerDefinition(): ContainerDefinitionInterface
    {
        $handler = new ContainerDefinitionHandler([], ['main']);

        return ContainerDefinition::fromArray(
            [
                'parameterDefinitions' => $handler->getParameterDefinitions(),
                'viewTypes' => [
                    'large' => ViewType::fromArray(
                        [
                            'itemViewTypes' => [
                                'standard' => new ItemViewType(),
                            ],
                        ]
                    ),
                ],
            ]
        );
    }
}
