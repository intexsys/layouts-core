<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Tests\Parameters;

use Netgen\BlockManager\Exception\BadMethodCallException;
use Netgen\BlockManager\Exception\Parameters\ParameterBuilderException;
use Netgen\BlockManager\Parameters\CompoundParameterDefinition;
use Netgen\BlockManager\Parameters\ParameterBuilderFactory;
use Netgen\BlockManager\Parameters\ParameterBuilderInterface;
use Netgen\BlockManager\Parameters\ParameterDefinition;
use Netgen\BlockManager\Parameters\ParameterType;
use Netgen\BlockManager\Parameters\Registry\ParameterTypeRegistry;
use Netgen\BlockManager\Tests\TestCase\ExportObjectTrait;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ParameterBuilderTest extends TestCase
{
    use ExportObjectTrait;

    /**
     * @var \Netgen\BlockManager\Parameters\Registry\ParameterTypeRegistryInterface
     */
    private $registry;

    /**
     * @var \Netgen\BlockManager\Parameters\ParameterBuilderFactory
     */
    private $factory;

    /**
     * @var \Netgen\BlockManager\Parameters\ParameterBuilderInterface
     */
    private $builder;

    public function setUp(): void
    {
        $this->registry = new ParameterTypeRegistry(
            new ParameterType\TextType(),
            new ParameterType\IntegerType(),
            new ParameterType\Compound\BooleanType()
        );

        $this->factory = new ParameterBuilderFactory($this->registry);

        $this->builder = $this->factory->createParameterBuilder();
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::__construct
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getName
     */
    public function testGetName(): void
    {
        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
            ]
        );

        self::assertSame('test', $this->builder->get('test')->getName());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getType
     */
    public function testGetType(): void
    {
        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
            ]
        );

        self::assertSame(
            $this->registry->getParameterType('text'),
            $this->builder->get('test')->getType()
        );
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getOptions
     */
    public function testGetOptions(): void
    {
        $this->builder->add(
            'test',
            ParameterType\Compound\BooleanType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
                'reverse' => true,
            ]
        );

        self::assertSame(
            ['reverse' => true],
            $this->builder->get('test')->getOptions()
        );
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getOption
     */
    public function testGetOption(): void
    {
        $this->builder->add(
            'test',
            ParameterType\Compound\BooleanType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
                'reverse' => true,
            ]
        );

        self::assertTrue($this->builder->get('test')->getOption('reverse'));
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getOption
     */
    public function testGetOptionThrowsParameterBuilderException(): void
    {
        $this->expectException(ParameterBuilderException::class);
        $this->expectExceptionMessage('Option "unknown" does not exist in the builder for "test" parameter.');

        $this->builder->add(
            'test',
            ParameterType\Compound\BooleanType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
                'reverse' => true,
            ]
        );

        self::assertTrue($this->builder->get('test')->getOption('unknown'));
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::hasOption
     */
    public function testHasOption(): void
    {
        $this->builder->add(
            'test',
            ParameterType\Compound\BooleanType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
                'reverse' => true,
            ]
        );

        self::assertTrue($this->builder->get('test')->hasOption('reverse'));
        self::assertFalse($this->builder->get('test')->hasOption('unknown'));
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setOption
     */
    public function testSetOption(): void
    {
        $this->builder->add(
            'test',
            ParameterType\IntegerType::class,
            [
                'min' => 5,
                'max' => 100,
            ]
        );

        $this->builder->get('test')->setOption('min', 42);

        self::assertSame(42, $this->builder->get('test')->getOption('min'));
        self::assertSame(100, $this->builder->get('test')->getOption('max'));
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setOption
     */
    public function testSetRequiredOption(): void
    {
        $this->builder->add(
            'test',
            ParameterType\IntegerType::class,
            [
                'required' => true,
            ]
        );

        $this->builder->get('test')->setOption('required', false);

        self::assertFalse($this->builder->get('test')->isRequired());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setOption
     */
    public function testSetDefaultValueOption(): void
    {
        $this->builder->add(
            'test',
            ParameterType\IntegerType::class,
            [
                'default_value' => 'test',
            ]
        );

        $this->builder->get('test')->setOption('default_value', 'test2');

        self::assertSame('test2', $this->builder->get('test')->getDefaultValue());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setOption
     */
    public function testSetLabelOption(): void
    {
        $this->builder->add(
            'test',
            ParameterType\IntegerType::class,
            [
                'label' => 'test',
            ]
        );

        $this->builder->get('test')->setOption('label', 'test2');

        self::assertSame('test2', $this->builder->get('test')->getLabel());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setOption
     */
    public function testSetGroupsOption(): void
    {
        $this->builder->add(
            'test',
            ParameterType\IntegerType::class,
            [
                'groups' => ['test'],
            ]
        );

        $this->builder->get('test')->setOption('groups', ['test2']);

        self::assertSame(['test2'], $this->builder->get('test')->getGroups());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setOption
     */
    public function testSetOptionAfterBuildingParameters(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Setting the options is not possible after parameters have been built.');

        $this->builder->buildParameterDefinitions();
        $this->builder->setOption('required', true);
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::isRequired
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setRequired
     */
    public function testGetSetRequired(): void
    {
        $this->builder->add(
            'test',
            ParameterType\TextType::class
        );

        $this->builder->get('test')->setRequired(true);

        self::assertTrue($this->builder->get('test')->isRequired());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setRequired
     */
    public function testSetRequiredAfterBuildingParameters(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Setting the required flag is not possible after parameters have been built.');

        $this->builder->buildParameterDefinitions();
        $this->builder->setRequired(true);
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getDefaultValue
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setDefaultValue
     */
    public function testGetSetDefaultValue(): void
    {
        $this->builder->add(
            'test',
            ParameterType\TextType::class
        );

        $this->builder->get('test')->setDefaultValue(42);

        self::assertSame(42, $this->builder->get('test')->getDefaultValue());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setDefaultValue
     */
    public function testSetDefaultValueAfterBuildingParameters(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Setting the default value is not possible after parameters have been built.');

        $this->builder->buildParameterDefinitions();
        $this->builder->setDefaultValue('test');
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getLabel
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setLabel
     */
    public function testGetSetLabel(): void
    {
        $this->builder->add(
            'test',
            ParameterType\TextType::class
        );

        $this->builder->get('test')->setLabel('Custom label');

        self::assertSame('Custom label', $this->builder->get('test')->getLabel());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setLabel
     */
    public function testSetLabelAfterBuildingParameters(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Setting the label is not possible after parameters have been built.');

        $this->builder->buildParameterDefinitions();
        $this->builder->setLabel('test');
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getGroups
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setGroups
     */
    public function testGetSetGroups(): void
    {
        $this->builder->add(
            'test',
            ParameterType\TextType::class
        );

        $this->builder->get('test')->setGroups(['group']);

        self::assertSame(['group'], $this->builder->get('test')->getGroups());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getGroups
     */
    public function testGetGroupsWithoutParentBuilder(): void
    {
        self::assertSame([], $this->builder->getGroups());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getConstraints
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setConstraints
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::validateConstraints
     */
    public function testGetSetConstraints(): void
    {
        $constraints = [new NotBlank(), static function (): void {}];

        $this->builder->add(
            'test',
            ParameterType\TextType::class
        );

        $this->builder->get('test')->setConstraints($constraints);

        self::assertSame($constraints, $this->builder->get('test')->getConstraints());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getConstraints
     */
    public function testGetConstraintsWithoutParentBuilder(): void
    {
        self::assertSame([], $this->builder->getConstraints());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setConstraints
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::validateConstraints
     */
    public function testSetConstraintsWithInvalidConstraints(): void
    {
        $this->expectException(ParameterBuilderException::class);
        $this->expectExceptionMessage('Parameter constraints need to be either a Symfony constraint or a closure.');

        $this->builder->setConstraints([new stdClass()]);
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setConstraints
     */
    public function testSetConstraintsAfterBuildingParameters(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Setting the constraints is not possible after parameters have been built.');

        $this->builder->buildParameterDefinitions();
        $this->builder->setConstraints([]);
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::getGroups
     */
    public function testGetGroupsWithCompoundParameter(): void
    {
        $this->builder->add(
            'test',
            ParameterType\Compound\BooleanType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
                'reverse' => true,
            ]
        );

        $this->builder->get('test')->add(
            'test2',
            ParameterType\TextType::class,
            [
                'groups' => ['group2'],
            ]
        );

        self::assertSame(['group'], $this->builder->get('test')->get('test2')->getGroups());
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::setGroups
     */
    public function testSetGroupsAfterBuildingParameters(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Setting the groups is not possible after parameters have been built.');

        $this->builder->buildParameterDefinitions();
        $this->builder->setGroups([]);
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::add
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::count
     */
    public function testAdd(): void
    {
        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
                'constraints' => [new NotBlank()],
            ]
        );

        $this->builder->add(
            'test2',
            ParameterType\TextType::class,
            [
                'required' => false,
                'default_value' => 'test value 2',
                'groups' => ['group 2'],
            ]
        );

        self::assertCount(2, $this->builder);
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::add
     */
    public function testAddAfterBuildingParameters(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Parameters cannot be added after they have been built.');

        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
            ]
        );

        $this->builder->buildParameterDefinitions();

        $this->builder->add(
            'test2',
            ParameterType\TextType::class,
            [
                'required' => false,
                'default_value' => 'test value 2',
                'groups' => ['group 2'],
            ]
        );
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::add
     */
    public function testAddThrowsParameterBuilderExceptionOnAddingParameterToNonCompoundParameter(): void
    {
        $this->expectException(ParameterBuilderException::class);
        $this->expectExceptionMessage('Parameters cannot be added to non-compound parameters.');

        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
            ]
        );

        $this->builder->get('test')->add(
            'test2',
            ParameterType\TextType::class,
            [
                'required' => false,
                'default_value' => 'test value 2',
                'groups' => ['group 2'],
            ]
        );
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::add
     */
    public function testAddThrowsParameterBuilderExceptionOnAddingCompoundParameterToCompoundParameter(): void
    {
        $this->expectException(ParameterBuilderException::class);
        $this->expectExceptionMessage('Compound parameters cannot be added to compound parameters.');

        $this->builder->add(
            'test',
            ParameterType\Compound\BooleanType::class
        );

        $this->builder->get('test')->add(
            'test2',
            ParameterType\Compound\BooleanType::class
        );
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::add
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::has
     */
    public function testHas(): void
    {
        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
            ]
        );

        $this->builder->add(
            'test2',
            ParameterType\TextType::class,
            [
                'required' => false,
                'default_value' => 'test value 2',
                'groups' => ['group 2'],
            ]
        );

        self::assertTrue($this->builder->has('test'));
        self::assertTrue($this->builder->has('test2'));

        self::assertFalse($this->builder->has('unknown'));
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::add
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::get
     */
    public function testGet(): void
    {
        $this->builder->add(
            'test',
            ParameterType\Compound\BooleanType::class,
            [
                'required' => true,
                'default_value' => true,
                'groups' => ['group'],
            ]
        );

        $this::assertTrue($this->builder->has('test'));
        $this->builder->get('test');
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::add
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::get
     */
    public function testGetThrowsParameterBuilderExceptionWithNonExistingParameter(): void
    {
        $this->expectException(ParameterBuilderException::class);
        $this->expectExceptionMessage('Parameter with "unknown" name does not exist in the builder.');

        $this->builder->add(
            'test',
            ParameterType\Compound\BooleanType::class,
            [
                'required' => true,
                'default_value' => true,
                'groups' => ['group'],
            ]
        );

        $this->builder->get('unknown');
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::add
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::get
     */
    public function testGetAfterBuildingParameters(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Accessing parameter builders is not possible after parameters have been built.');

        $this->builder->add(
            'test',
            ParameterType\Compound\BooleanType::class,
            [
                'required' => true,
                'default_value' => true,
                'groups' => ['group'],
            ]
        );

        $this->builder->buildParameterDefinitions();

        $this->builder->get('test');
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::all
     */
    public function testAll(): void
    {
        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'groups' => ['group'],
            ]
        );

        $this->builder->add(
            'test2',
            ParameterType\TextType::class,
            [
                'groups' => ['group2'],
            ]
        );

        $parameterBuilders = $this->builder->all();

        self::assertCount(2, $parameterBuilders);
        self::assertArrayHasKey('test', $parameterBuilders);
        self::assertArrayHasKey('test2', $parameterBuilders);

        self::assertContainsOnlyInstancesOf(ParameterBuilderInterface::class, $parameterBuilders);
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::all
     */
    public function testAllWithGroupFilter(): void
    {
        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'groups' => ['group'],
            ]
        );

        $this->builder->add(
            'test2',
            ParameterType\TextType::class,
            [
                'groups' => ['group2'],
            ]
        );

        $parameterBuilders = $this->builder->all('group');

        self::assertCount(1, $parameterBuilders);
        self::assertArrayHasKey('test', $parameterBuilders);
        self::assertContainsOnlyInstancesOf(ParameterBuilderInterface::class, $parameterBuilders);
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::all
     */
    public function testAllAfterBuildingParameters(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Accessing parameter builders is not possible after parameters have been built.');

        $this->builder->add('test', ParameterType\TextType::class);

        $this->builder->buildParameterDefinitions();

        $this->builder->all();
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::add
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::remove
     */
    public function testRemove(): void
    {
        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
            ]
        );

        $this->builder->remove('test');

        self::assertCount(0, $this->builder);
        self::assertFalse($this->builder->has('test'));
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::add
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::remove
     */
    public function testRemoveAfterBuildingParameters(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Removing parameters is not possible after parameters have been built.');

        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'groups' => ['group'],
            ]
        );

        $this->builder->buildParameterDefinitions();

        $this->builder->remove('test');
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinition
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinitions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::configureOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::resolveOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::validateConstraints
     */
    public function testBuildParameterDefinitions(): void
    {
        $constraints = [new NotBlank(), static function (): void {}];

        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'label' => null,
                'groups' => ['group'],
                'constraints' => $constraints,
            ]
        );

        $this->builder->add(
            'compound',
            ParameterType\Compound\BooleanType::class,
            [
                'required' => false,
                'default_value' => true,
                'label' => false,
                'groups' => ['group 2'],
            ]
        );

        $this->builder->get('compound')->add(
            'test2',
            ParameterType\TextType::class,
            [
                'required' => true,
                'default_value' => 'test value 2',
                'label' => 'Custom label',
                'groups' => ['group'],
            ]
        );

        $parameterDefinitions = $this->builder->buildParameterDefinitions();

        self::assertArrayHasKey('test', $parameterDefinitions);
        self::assertArrayHasKey('compound', $parameterDefinitions);

        self::assertContainsOnlyInstancesOf(ParameterDefinition::class, $parameterDefinitions);
        self::assertNotInstanceOf(CompoundParameterDefinition::class, $parameterDefinitions['test']);

        $compoundDefinition = $parameterDefinitions['compound'];
        self::assertInstanceOf(CompoundParameterDefinition::class, $compoundDefinition);

        $innerDefinitions = $compoundDefinition->getParameterDefinitions();

        self::assertArrayHasKey('test2', $innerDefinitions);

        self::assertContainsOnlyInstancesOf(ParameterDefinition::class, $innerDefinitions);
        self::assertNotInstanceOf(CompoundParameterDefinition::class, $innerDefinitions['test2']);

        self::assertSame(
            [
                'name' => 'test',
                'type' => $this->registry->getParameterType('text'),
                'options' => [],
                'isRequired' => true,
                'defaultValue' => 'test value',
                'label' => null,
                'groups' => ['group'],
                'constraints' => $constraints,
            ],
            $this->exportObject($parameterDefinitions['test'])
        );

        self::assertSame(
            [
                'name' => 'compound',
                'type' => $this->registry->getParameterType('compound_boolean'),
                'options' => ['reverse' => false],
                'isRequired' => false,
                'defaultValue' => true,
                'label' => false,
                'groups' => ['group 2'],
                'constraints' => [],
                'parameterDefinitions' => $innerDefinitions,
            ],
            $this->exportObject($parameterDefinitions['compound'])
        );

        self::assertSame(
            [
                'name' => 'test2',
                'type' => $this->registry->getParameterType('text'),
                'options' => [],
                'isRequired' => true,
                'defaultValue' => 'test value 2',
                'label' => 'Custom label',
                'groups' => ['group 2'],
                'constraints' => [],
            ],
            $this->exportObject($innerDefinitions['test2'])
        );
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinition
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinitions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::configureOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::resolveOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::validateConstraints
     */
    public function testBuildParameterDefinitionsAfterBuildingParameters(): void
    {
        $constraints = [new NotBlank()];

        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'required' => true,
                'default_value' => 'test value',
                'label' => null,
                'groups' => ['group'],
                'constraints' => $constraints,
            ]
        );

        $this->builder->buildParameterDefinitions();

        $parameterDefinitions = $this->builder->buildParameterDefinitions();

        self::assertArrayHasKey('test', $parameterDefinitions);
        self::assertContainsOnlyInstancesOf(ParameterDefinition::class, $parameterDefinitions);

        self::assertSame(
            [
                'name' => 'test',
                'type' => $this->registry->getParameterType('text'),
                'options' => [],
                'isRequired' => true,
                'defaultValue' => 'test value',
                'label' => null,
                'groups' => ['group'],
                'constraints' => $constraints,
            ],
            $this->exportObject($parameterDefinitions['test'])
        );
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinition
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinitions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::configureOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::resolveOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::validateConstraints
     */
    public function testBuildParameterDefinitionsWithDefaultOptions(): void
    {
        $this->builder->add('test', ParameterType\TextType::class);

        $parameterDefinitions = $this->builder->buildParameterDefinitions();

        self::assertArrayHasKey('test', $parameterDefinitions);
        self::assertContainsOnlyInstancesOf(ParameterDefinition::class, $parameterDefinitions);

        self::assertSame(
            [
                'name' => 'test',
                'type' => $this->registry->getParameterType('text'),
                'options' => [],
                'isRequired' => false,
                'defaultValue' => null,
                'label' => null,
                'groups' => [],
                'constraints' => [],
            ],
            $this->exportObject($parameterDefinitions['test'])
        );
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinition
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinitions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::configureOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::resolveOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::validateConstraints
     */
    public function testBuildParameterDefinitionsWithInvalidRequiredOption(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "required" with value "true" is expected to be of type "bool", but is of type "string".');

        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'required' => 'true',
            ]
        );

        $this->builder->buildParameterDefinitions();
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinition
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinitions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::configureOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::resolveOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::validateConstraints
     */
    public function testBuildParameterDefinitionsWithInvalidGroupsOption(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "groups" with value "group" is expected to be of type "string[]", but is of type "string".');

        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'groups' => 'group',
            ]
        );

        $this->builder->buildParameterDefinitions();
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinition
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinitions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::configureOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::resolveOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::validateConstraints
     */
    public function testBuildParameterDefinitionsWithInvalidGroup(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "groups" with value array is expected to be of type "string[]", but one of the elements is of type "integer[]".');

        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'groups' => [42],
            ]
        );

        $this->builder->buildParameterDefinitions();
    }

    /**
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinition
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::buildParameterDefinitions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::configureOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::resolveOptions
     * @covers \Netgen\BlockManager\Parameters\ParameterBuilder::validateConstraints
     */
    public function testBuildParameterDefinitionsWithInvalidLabel(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "label" with value true is invalid.');

        $this->builder->add(
            'test',
            ParameterType\TextType::class,
            [
                'label' => true,
            ]
        );

        $this->builder->buildParameterDefinitions();
    }
}
