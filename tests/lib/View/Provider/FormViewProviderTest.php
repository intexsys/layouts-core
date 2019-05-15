<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\View\Provider;

use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\Tests\API\Stubs\Value;
use Netgen\Layouts\View\Provider\FormViewProvider;
use Netgen\Layouts\View\View\FormViewInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final class FormViewProviderTest extends TestCase
{
    /**
     * @var \Netgen\Layouts\View\Provider\ViewProviderInterface
     */
    private $formViewProvider;

    protected function setUp(): void
    {
        $this->formViewProvider = new FormViewProvider();
    }

    /**
     * @covers \Netgen\Layouts\View\Provider\FormViewProvider::provideView
     */
    public function testProvideView(): void
    {
        $formView = new FormView();
        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())
            ->method('createView')
            ->willReturn($formView);

        $view = $this->formViewProvider->provideView($form);

        self::assertInstanceOf(FormViewInterface::class, $view);

        self::assertSame($form, $view->getForm());
        self::assertNull($view->getTemplate());
        self::assertSame(
            [
                'form_object' => $form,
                'form' => $formView,
            ],
            $view->getParameters()
        );
    }

    /**
     * @param mixed $value
     * @param bool $supports
     *
     * @covers \Netgen\Layouts\View\Provider\FormViewProvider::supports
     * @dataProvider supportsProvider
     */
    public function testSupports($value, bool $supports): void
    {
        self::assertSame($supports, $this->formViewProvider->supports($value));
    }

    public function supportsProvider(): array
    {
        return [
            [new Value(), false],
            [$this->createMock(FormInterface::class), true],
            [new Layout(), false],
        ];
    }
}
