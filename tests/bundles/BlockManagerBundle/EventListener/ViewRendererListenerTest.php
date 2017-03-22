<?php

namespace Netgen\Bundle\BlockManagerBundle\Tests\EventListener;

use Netgen\BlockManager\Tests\Core\Stubs\Value;
use Netgen\BlockManager\Tests\View\Stubs\View;
use Netgen\BlockManager\View\ViewRendererInterface;
use Netgen\Bundle\BlockManagerBundle\EventListener\ViewRendererListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewRendererListenerTest extends TestCase
{
    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\EventListener\ViewRendererListener::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $viewRendererMock = $this->createMock(ViewRendererInterface::class);
        $eventListener = new ViewRendererListener($viewRendererMock);

        $this->assertEquals(
            array(KernelEvents::VIEW => array('onView', -255)),
            $eventListener->getSubscribedEvents()
        );
    }

    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\EventListener\ViewRendererListener::__construct
     * @covers \Netgen\Bundle\BlockManagerBundle\EventListener\ViewRendererListener::onView
     */
    public function testOnView()
    {
        $view = new View(array('value' => new Value()));

        $response = new Response();
        $response->headers->set('X-NGBM-Test', 'test');
        $view->setResponse($response);

        $viewRendererMock = $this->createMock(ViewRendererInterface::class);
        $viewRendererMock
            ->expects($this->once())
            ->method('renderView')
            ->with($this->equalTo($view))
            ->will($this->returnValue('rendered content'));

        $eventListener = new ViewRendererListener($viewRendererMock);

        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');

        $event = new GetResponseForControllerResultEvent(
            $kernelMock,
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $view
        );

        $eventListener->onView($event);

        $this->assertInstanceOf(
            Response::class,
            $event->getResponse()
        );

        // Verify that we use the response available in view object
        $this->assertEquals(
            $event->getResponse()->headers->get('X-NGBM-Test'),
            'test'
        );

        $this->assertEquals(
            'rendered content',
            $event->getResponse()->getContent()
        );
    }

    /**
     * @covers \Netgen\Bundle\BlockManagerBundle\EventListener\ViewRendererListener::onView
     */
    public function testOnViewWithoutSupportedValue()
    {
        $viewRendererMock = $this->createMock(ViewRendererInterface::class);
        $eventListener = new ViewRendererListener($viewRendererMock);

        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');

        $event = new GetResponseForControllerResultEvent(
            $kernelMock,
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            42
        );

        $eventListener->onView($event);

        $this->assertNull($event->getResponse());
    }
}
