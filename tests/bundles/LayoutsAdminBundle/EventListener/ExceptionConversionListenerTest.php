<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\Tests\EventListener;

use Exception;
use InvalidArgumentException as BaseInvalidArgumentException;
use Netgen\Bundle\LayoutsAdminBundle\EventListener\ExceptionConversionListener;
use Netgen\Bundle\LayoutsAdminBundle\EventListener\SetIsApiRequestListener;
use Netgen\Bundle\LayoutsAdminBundle\Tests\EventListener\Stubs\ExceptionStub;
use Netgen\Layouts\Exception\API\ConfigException;
use Netgen\Layouts\Exception\BadStateException;
use Netgen\Layouts\Exception\InvalidArgumentException;
use Netgen\Layouts\Exception\NotFoundException;
use Netgen\Layouts\Exception\Validation\ValidationException;
use Netgen\Layouts\Exception\View\ViewException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class ExceptionConversionListenerTest extends TestCase
{
    /**
     * @var \Netgen\Bundle\LayoutsAdminBundle\EventListener\ExceptionConversionListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->listener = new ExceptionConversionListener();
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\EventListener\ExceptionConversionListener::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        self::assertSame(
            [KernelEvents::EXCEPTION => ['onException', 10]],
            $this->listener::getSubscribedEvents()
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\EventListener\ExceptionConversionListener::onException
     * @dataProvider onExceptionDataProvider
     */
    public function testOnException(Exception $exception, string $convertedClass, int $statusCode, bool $converted): void
    {
        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');
        $request->attributes->set(SetIsApiRequestListener::API_FLAG_NAME, true);

        $event = new GetResponseForExceptionEvent(
            $kernelMock,
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );

        $this->listener->onException($event);

        self::assertInstanceOf(
            $convertedClass,
            $event->getException()
        );

        self::assertSame($exception->getMessage(), $event->getException()->getMessage());
        self::assertSame($exception->getCode(), $event->getException()->getCode());

        if ($event->getException() instanceof HttpExceptionInterface) {
            self::assertSame($statusCode, $event->getException()->getStatusCode());
        }

        $converted ?
            self::assertSame($exception, $event->getException()->getPrevious()) :
            self::assertSame($exception, $event->getException());
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\EventListener\ExceptionConversionListener::onException
     */
    public function testOnExceptionNotConvertsOtherExceptions(): void
    {
        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');
        $request->attributes->set(SetIsApiRequestListener::API_FLAG_NAME, true);
        $exception = new RuntimeException('Some error');

        $event = new GetResponseForExceptionEvent(
            $kernelMock,
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );

        $this->listener->onException($event);

        self::assertInstanceOf(
            RuntimeException::class,
            $event->getException()
        );
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\EventListener\ExceptionConversionListener::onException
     */
    public function testOnExceptionInSubRequest(): void
    {
        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');
        $request->attributes->set(SetIsApiRequestListener::API_FLAG_NAME, true);
        $exception = new NotFoundException('param', 'Some error');

        $event = new GetResponseForExceptionEvent(
            $kernelMock,
            $request,
            HttpKernelInterface::SUB_REQUEST,
            $exception
        );

        $this->listener->onException($event);

        self::assertSame($exception, $event->getException());
    }

    /**
     * @covers \Netgen\Bundle\LayoutsAdminBundle\EventListener\ExceptionConversionListener::onException
     */
    public function testOnExceptionWithNonAPIRequest(): void
    {
        $kernelMock = $this->createMock(HttpKernelInterface::class);
        $request = Request::create('/');
        $exception = new NotFoundException('param', 'Some error');

        $event = new GetResponseForExceptionEvent(
            $kernelMock,
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );

        $this->listener->onException($event);

        self::assertSame($exception, $event->getException());
    }

    public function onExceptionDataProvider(): array
    {
        return [
            [
                new NotFoundException('param', 'Some error'),
                NotFoundHttpException::class,
                Response::HTTP_NOT_FOUND,
                true,
            ],
            [
                new InvalidArgumentException('param', 'Some error'),
                BadRequestHttpException::class,
                Response::HTTP_BAD_REQUEST,
                true,
            ],
            [
                new BadStateException('param', 'Some error'),
                UnprocessableEntityHttpException::class,
                Response::HTTP_UNPROCESSABLE_ENTITY,
                true,
            ],
            [
                new ValidationException('Some error'),
                BadRequestHttpException::class,
                Response::HTTP_BAD_REQUEST,
                true,
            ],
            [
                new ConfigException('Some error'),
                BadRequestHttpException::class,
                Response::HTTP_BAD_REQUEST,
                true,
            ],
            [
                new ExceptionStub('Some error'),
                ExceptionStub::class,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                false,
            ],
            [
                new Exception('Some error'),
                Exception::class,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                false,
            ],
            [
                new AccessDeniedException('Some error'),
                AccessDeniedHttpException::class,
                Response::HTTP_FORBIDDEN,
                true,
            ],
            [
                new BaseInvalidArgumentException('Some error'),
                BadRequestHttpException::class,
                Response::HTTP_BAD_REQUEST,
                true,
            ],
            [
                new ViewException('Some error'),
                ViewException::class,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                false,
            ],
            [
                new RuntimeException('Some error'),
                RuntimeException::class,
                Response::HTTP_INTERNAL_SERVER_ERROR,
                false,
            ],
            [
                new AccessDeniedHttpException('Some error'),
                AccessDeniedHttpException::class,
                Response::HTTP_FORBIDDEN,
                false,
            ],
        ];
    }
}
