<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Tests\Collection\Result\Pagerfanta\View;

use Netgen\BlockManager\API\Values\Block\Block;
use Netgen\BlockManager\Collection\Result\Pagerfanta\View\CollectionView;
use Netgen\BlockManager\Exception\InvalidArgumentException;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

final class CollectionViewTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $twigMock;

    /**
     * @var \Netgen\BlockManager\Collection\Result\Pagerfanta\View\CollectionView
     */
    private $collectionView;

    public function setUp(): void
    {
        $this->twigMock = $this->createMock(Environment::class);

        $this->collectionView = new CollectionView($this->twigMock, 'default_template.html.twig');
    }

    /**
     * @covers \Netgen\BlockManager\Collection\Result\Pagerfanta\View\CollectionView::__construct
     * @covers \Netgen\BlockManager\Collection\Result\Pagerfanta\View\CollectionView::getName
     */
    public function testGetName(): void
    {
        self::assertSame('ngbm_collection', $this->collectionView->getName());
    }

    /**
     * @covers \Netgen\BlockManager\Collection\Result\Pagerfanta\View\CollectionView::render
     */
    public function testRender(): void
    {
        $block = new Block();
        $pagerMock = $this->createMock(Pagerfanta::class);

        $this->twigMock->expects(self::once())
            ->method('render')
            ->with(
                self::identicalTo('default_template.html.twig'),
                self::identicalTo(
                    [
                        'pager' => $pagerMock,
                        'block' => $block,
                        'collection_identifier' => 'default',
                    ]
                )
            )
            ->willReturn('rendered template');

        $renderedTemplate = $this->collectionView->render(
            $pagerMock,
            $this->getRouteGenerator(),
            [
                'block' => $block,
                'collection_identifier' => 'default',
            ]
        );

        self::assertSame('rendered template', $renderedTemplate);
    }

    /**
     * @covers \Netgen\BlockManager\Collection\Result\Pagerfanta\View\CollectionView::render
     */
    public function testRenderWithOverriddenTemplate(): void
    {
        $block = new Block();
        $pagerMock = $this->createMock(Pagerfanta::class);

        $this->twigMock->expects(self::once())
            ->method('render')
            ->with(
                self::identicalTo('template.html.twig'),
                self::identicalTo(
                    [
                        'pager' => $pagerMock,
                        'block' => $block,
                        'collection_identifier' => 'default',
                    ]
                )
            )
            ->willReturn('rendered template');

        $renderedTemplate = $this->collectionView->render(
            $pagerMock,
            $this->getRouteGenerator(),
            [
                'block' => $block,
                'collection_identifier' => 'default',
                'template' => 'template.html.twig',
            ]
        );

        self::assertSame('rendered template', $renderedTemplate);
    }

    /**
     * @covers \Netgen\BlockManager\Collection\Result\Pagerfanta\View\CollectionView::render
     */
    public function testRenderThrowsInvalidArgumentExceptionWithNoBlock(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('To render the collection view, "block" option must be an instance of Netgen\\BlockManager\\API\\Values\\Block\\Block');

        $pagerMock = $this->createMock(Pagerfanta::class);

        $this->twigMock->expects(self::never())
            ->method('render');

        $this->collectionView->render(
            $pagerMock,
            $this->getRouteGenerator(),
            [
                'collection_identifier' => 'default',
            ]
        );
    }

    /**
     * @covers \Netgen\BlockManager\Collection\Result\Pagerfanta\View\CollectionView::render
     */
    public function testRenderThrowsInvalidArgumentExceptionWithInvalidBlock(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('To render the collection view, "block" option must be an instance of Netgen\\BlockManager\\API\\Values\\Block\\Block');

        $pagerMock = $this->createMock(Pagerfanta::class);

        $this->twigMock->expects(self::never())
            ->method('render');

        $this->collectionView->render(
            $pagerMock,
            $this->getRouteGenerator(),
            [
                'block' => 'block',
                'collection_identifier' => 'default',
            ]
        );
    }

    /**
     * @covers \Netgen\BlockManager\Collection\Result\Pagerfanta\View\CollectionView::render
     */
    public function testRenderThrowsInvalidArgumentExceptionWithNoCollectionIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('To render the collection view, "collection_identifier" option must be a string');

        $pagerMock = $this->createMock(Pagerfanta::class);

        $this->twigMock->expects(self::never())
            ->method('render');

        $this->collectionView->render(
            $pagerMock,
            $this->getRouteGenerator(),
            [
                'block' => new Block(),
            ]
        );
    }

    /**
     * @covers \Netgen\BlockManager\Collection\Result\Pagerfanta\View\CollectionView::render
     */
    public function testRenderThrowsInvalidArgumentExceptionWithInvalidCollectionIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('To render the collection view, "collection_identifier" option must be a string');

        $pagerMock = $this->createMock(Pagerfanta::class);

        $this->twigMock->expects(self::never())
            ->method('render');

        $this->collectionView->render(
            $pagerMock,
            $this->getRouteGenerator(),
            [
                'block' => new Block(),
                'collection_identifier' => 42,
            ]
        );
    }

    private function getRouteGenerator(): callable
    {
        return function (Block $block, string $collectionIdentifier, int $page): string {
            return '/route/' . $page;
        };
    }
}
