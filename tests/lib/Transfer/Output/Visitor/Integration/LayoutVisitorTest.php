<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Transfer\Output\Visitor\Integration;

use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\API\Values\Layout\Zone;
use Netgen\Layouts\Exception\RuntimeException;
use Netgen\Layouts\Transfer\Output\Visitor\LayoutVisitor;
use Netgen\Layouts\Transfer\Output\VisitorInterface;

abstract class LayoutVisitorTest extends VisitorTest
{
    public function testVisitThrowsRuntimeExceptionWithoutSubVisitor(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Implementation requires sub-visitor');

        $this->getVisitor()->visit(new Layout());
    }

    public function getVisitor(): VisitorInterface
    {
        return new LayoutVisitor();
    }

    public function acceptProvider(): array
    {
        return [
            [new Layout(), true],
            [new Zone(), false],
            [new Block(), false],
        ];
    }

    public function visitProvider(): array
    {
        return [
            [function (): Layout { return $this->layoutService->loadLayout(1); }, 'layout/layout_1.json'],
            [function (): Layout { return $this->layoutService->loadLayout(2); }, 'layout/layout_2.json'],
            [function (): Layout { return $this->layoutService->loadLayout(5); }, 'layout/layout_5.json'],
            [function (): Layout { return $this->layoutService->loadLayoutDraft(7); }, 'layout/layout_7.json'],
        ];
    }
}
