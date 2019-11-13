<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\Tests\EventListener;

use Exception;
use Netgen\Bundle\LayoutsAdminBundle\EventListener\AdminAuthenticationExceptionListener;
use Netgen\Bundle\LayoutsAdminBundle\EventListener\SetIsAdminRequestListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class AdminAuthenticationExceptionListenerTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\LayoutsAdminBundle\EventListener\AdminAuthenticationExceptionListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->listener = new AdminAuthenticationExceptionListener();
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\EventListener\AdminAuthenticationExceptionListener::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        self::assertSame(
            [KernelEvents::EXCEPTION => ['onException', 20]],
            $this->listener::getSubscribedEvents()
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\EventListener\AdminAuthenticationExceptionListener::onException
     */
    public function testOnException(): void
    {
        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->attributes->set(SetIsAdminRequestListener::ADMIN_FLAG_NAME, true);

        $event = new GetResponseForExceptionEvent(
            $kernelMock,
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            new AuthenticationException()
        );

        $this->listener->onException($event);

        // @deprecated Remove call to getException when support for Symfony 3.4 ends
        $eventException = method_exists($event, 'getThrowable') ? $event->getThrowable() : $event->getException();

        self::assertInstanceOf(AccessDeniedHttpException::class, $eventException);
        self::assertTrue($event->isPropagationStopped());
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\EventListener\AdminAuthenticationExceptionListener::onException
     */
    public function testOnExceptionWithWrongException(): void
    {
        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->attributes->set(SetIsAdminRequestListener::ADMIN_FLAG_NAME, true);

        $event = new GetResponseForExceptionEvent(
            $kernelMock,
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            new Exception()
        );

        $this->listener->onException($event);

        // @deprecated Remove call to getException when support for Symfony 3.4 ends
        $eventException = method_exists($event, 'getThrowable') ? $event->getThrowable() : $event->getException();

        self::assertNotInstanceOf(AccessDeniedHttpException::class, $eventException);
        self::assertFalse($event->isPropagationStopped());
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\EventListener\AdminAuthenticationExceptionListener::onException
     */
    public function testOnExceptionInNonAdminRequest(): void
    {
        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $event = new GetResponseForExceptionEvent(
            $kernelMock,
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            new Exception()
        );

        $this->listener->onException($event);

        // @deprecated Remove call to getException when support for Symfony 3.4 ends
        $eventException = method_exists($event, 'getThrowable') ? $event->getThrowable() : $event->getException();

        self::assertNotInstanceOf(AccessDeniedHttpException::class, $eventException);
        self::assertFalse($event->isPropagationStopped());
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\EventListener\AdminAuthenticationExceptionListener::onException
     */
    public function testOnExceptionInNonXmlHttpRequest(): void
    {
        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');
        $request->attributes->set(SetIsAdminRequestListener::ADMIN_FLAG_NAME, true);

        $event = new GetResponseForExceptionEvent(
            $kernelMock,
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            new Exception()
        );

        $this->listener->onException($event);

        // @deprecated Remove call to getException when support for Symfony 3.4 ends
        $eventException = method_exists($event, 'getThrowable') ? $event->getThrowable() : $event->getException();

        self::assertNotInstanceOf(AccessDeniedHttpException::class, $eventException);
        self::assertFalse($event->isPropagationStopped());
    }
}
