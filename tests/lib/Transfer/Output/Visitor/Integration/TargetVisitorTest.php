<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Transfer\Output\Visitor\Integration;

use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\API\Values\LayoutResolver\Target;
use Netgen\Layouts\Transfer\Output\Visitor\TargetVisitor;
use Netgen\Layouts\Transfer\Output\VisitorInterface;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Netgen\Layouts\Tests\Transfer\Output\Visitor\Integration\VisitorTest<\Netgen\Layouts\API\Values\LayoutResolver\Target>
 */
abstract class TargetVisitorTest extends VisitorTest
{
    public function getVisitor(): VisitorInterface
    {
        return new TargetVisitor();
    }

    public function acceptDataProvider(): array
    {
        return [
            [new Target(), true],
            [new Layout(), false],
            [new Block(), false],
        ];
    }

    public function visitDataProvider(): array
    {
        return [
            [fn (): Target => $this->layoutResolverService->loadTarget(Uuid::fromString('c7c5cdca-02da-5ba5-ad9e-d25cbc4b1b46')), 'target/target_1.json'],
            [fn (): Target => $this->layoutResolverService->loadTarget(Uuid::fromString('0cd23062-3fa7-582f-b022-034595ec68d5')), 'target/target_2.json'],
        ];
    }
}
