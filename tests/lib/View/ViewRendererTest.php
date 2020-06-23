<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\View;

use Netgen\Layouts\Event\CollectViewParametersEvent;
use Netgen\Layouts\Event\LayoutsEvents;
use Netgen\Layouts\Tests\API\Stubs\Value;
use Netgen\Layouts\Tests\View\Stubs\View;
use Netgen\Layouts\View\ViewRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Kernel;
use Twig\Environment;
use function array_reverse;
use function sprintf;

final class ViewRendererTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $eventDispatcherMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $twigEnvironmentMock;

    /**
     * @var \Netgen\Layouts\View\ViewRenderer
     */
    private $viewRenderer;

    protected function setUp(): void
    {
        $this->eventDispatcherMock = $this
            ->createMock(EventDispatcherInterface::class);

        $this->twigEnvironmentMock = $this
            ->createMock(Environment::class);

        $this->viewRenderer = new ViewRenderer(
            $this->eventDispatcherMock,
            $this->twigEnvironmentMock
        );
    }

    /**
     * @covers \Netgen\Layouts\View\ViewRenderer::__construct
     * @covers \Netgen\Layouts\View\ViewRenderer::renderView
     */
    public function testRenderView(): void
    {
        $value = new Value();
        $view = new View($value);
        $view->setTemplate('some_template.html.twig');
        $view->addParameter('some_param', 'some_value');

        $args = [
            self::isInstanceOf(CollectViewParametersEvent::class),
            self::identicalTo(LayoutsEvents::RENDER_VIEW),
        ];

        if (Kernel::VERSION_ID < 40300) {
            $args = array_reverse($args);
        }

        $consecutiveArgs = [$args];

        $args = [
            self::isInstanceOf(CollectViewParametersEvent::class),
            self::identicalTo(sprintf('%s.%s', LayoutsEvents::RENDER_VIEW, 'stub')),
        ];

        if (Kernel::VERSION_ID < 40300) {
            $args = array_reverse($args);
        }

        $consecutiveArgs[] = $args;

        $this->eventDispatcherMock
            ->method('dispatch')
            ->withConsecutive(...$consecutiveArgs);

        $this->twigEnvironmentMock
            ->expects(self::once())
            ->method('render')
            ->with(
                self::identicalTo('some_template.html.twig'),
                self::identicalTo(
                    [
                        'value' => $value,
                        'some_param' => 'some_value',
                    ]
                )
            )
            ->willReturn('rendered template');

        $renderedTemplate = $this->viewRenderer->renderView($view);

        self::assertSame('rendered template', $renderedTemplate);
    }

    /**
     * @covers \Netgen\Layouts\View\ViewRenderer::__construct
     * @covers \Netgen\Layouts\View\ViewRenderer::renderView
     */
    public function testRenderViewWithNoTemplate(): void
    {
        $view = new View(new Value());
        $view->addParameter('some_param', 'some_value');

        $args = [
            self::isInstanceOf(CollectViewParametersEvent::class),
            self::identicalTo(LayoutsEvents::RENDER_VIEW),
        ];

        if (Kernel::VERSION_ID < 40300) {
            $args = array_reverse($args);
        }

        $consecutiveArgs = [$args];

        $args = [
            self::isInstanceOf(CollectViewParametersEvent::class),
            self::identicalTo(sprintf('%s.%s', LayoutsEvents::RENDER_VIEW, 'stub')),
        ];

        if (Kernel::VERSION_ID < 40300) {
            $args = array_reverse($args);
        }

        $consecutiveArgs[] = $args;

        $this->eventDispatcherMock
            ->method('dispatch')
            ->withConsecutive(...$consecutiveArgs);

        $this->twigEnvironmentMock
            ->expects(self::never())
            ->method('render');

        $renderedTemplate = $this->viewRenderer->renderView($view);

        self::assertSame('', $renderedTemplate);
    }
}
