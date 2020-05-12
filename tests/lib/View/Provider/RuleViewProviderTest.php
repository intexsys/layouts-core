<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\View\Provider;

use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\API\Values\LayoutResolver\Rule;
use Netgen\Layouts\Tests\API\Stubs\Value;
use Netgen\Layouts\View\Provider\RuleViewProvider;
use Netgen\Layouts\View\View\RuleViewInterface;
use PHPUnit\Framework\TestCase;

final class RuleViewProviderTest extends TestCase
{
    /**
     * @var \Netgen\Layouts\View\Provider\ViewProviderInterface
     */
    private $ruleViewProvider;

    protected function setUp(): void
    {
        $this->ruleViewProvider = new RuleViewProvider();
    }

    /**
     * @covers \Netgen\Layouts\View\Provider\RuleViewProvider::provideView
     */
    public function testProvideView(): void
    {
        $rule = Rule::fromArray(['id' => 42]);

        $view = $this->ruleViewProvider->provideView($rule);

        self::assertInstanceOf(RuleViewInterface::class, $view);

        self::assertSame($rule, $view->getRule());
        self::assertNull($view->getTemplate());
        self::assertSame(
            [
                'rule' => $rule,
            ],
            $view->getParameters()
        );
    }

    /**
     * @param mixed $value
     *
     * @covers \Netgen\Layouts\View\Provider\RuleViewProvider::supports
     * @dataProvider supportsDataProvider
     */
    public function testSupports($value, bool $supports): void
    {
        self::assertSame($supports, $this->ruleViewProvider->supports($value));
    }

    public function supportsDataProvider(): array
    {
        return [
            [new Value(), false],
            [new Block(), false],
            [new Rule(), true],
        ];
    }
}
