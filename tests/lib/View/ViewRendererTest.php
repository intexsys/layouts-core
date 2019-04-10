<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Tests\View;

use Netgen\BlockManager\Event\BlockManagerEvents;
use Netgen\BlockManager\Event\CollectViewParametersEvent;
use Netgen\BlockManager\Tests\API\Stubs\Value;
use Netgen\BlockManager\Tests\View\Stubs\View;
use Netgen\BlockManager\View\ViewRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Kernel;
use Twig\Environment;

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
     * @var \Netgen\BlockManager\View\ViewRenderer
     */
    private $viewRenderer;

    public function setUp(): void
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
     * @covers \Netgen\BlockManager\View\ViewRenderer::__construct
     * @covers \Netgen\BlockManager\View\ViewRenderer::renderView
     */
    public function testRenderView(): void
    {
        $value = new Value();
        $view = new View($value);
        $view->setTemplate('some_template.html.twig');
        $view->addParameter('some_param', 'some_value');

        $args = [
            self::isInstanceOf(CollectViewParametersEvent::class),
            self::identicalTo(BlockManagerEvents::RENDER_VIEW),
        ];

        if (Kernel::VERSION_ID < 40300) {
            $args = array_reverse($args);
        }

        $this->eventDispatcherMock
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(...$args);

        $args = [
            self::isInstanceOf(CollectViewParametersEvent::class),
            self::identicalTo(sprintf('%s.%s', BlockManagerEvents::RENDER_VIEW, 'stub')),
        ];

        if (Kernel::VERSION_ID < 40300) {
            $args = array_reverse($args);
        }

        $this->eventDispatcherMock
            ->expects(self::at(1))
            ->method('dispatch')
            ->with(...$args);

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
     * @covers \Netgen\BlockManager\View\ViewRenderer::__construct
     * @covers \Netgen\BlockManager\View\ViewRenderer::renderView
     */
    public function testRenderViewWithNoTemplate(): void
    {
        $view = new View(new Value());
        $view->addParameter('some_param', 'some_value');

        $args = [
            self::isInstanceOf(CollectViewParametersEvent::class),
            self::identicalTo(BlockManagerEvents::RENDER_VIEW),
        ];

        if (Kernel::VERSION_ID < 40300) {
            $args = array_reverse($args);
        }

        $this->eventDispatcherMock
            ->expects(self::at(0))
            ->method('dispatch')
            ->with(...$args);

        $args = [
            self::isInstanceOf(CollectViewParametersEvent::class),
            self::identicalTo(sprintf('%s.%s', BlockManagerEvents::RENDER_VIEW, 'stub')),
        ];

        if (Kernel::VERSION_ID < 40300) {
            $args = array_reverse($args);
        }

        $this->eventDispatcherMock
            ->expects(self::at(1))
            ->method('dispatch')
            ->with(...$args);

        $this->twigEnvironmentMock
            ->expects(self::never())
            ->method('render');

        $renderedTemplate = $this->viewRenderer->renderView($view);

        self::assertSame('', $renderedTemplate);
    }
}
