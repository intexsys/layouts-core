<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Controller\API\V1\Layout;

use Netgen\Bundle\LayoutsBundle\Controller\AbstractController;
use Netgen\Layouts\API\Service\LayoutService;
use Netgen\Layouts\API\Values\Layout\Layout;
use Symfony\Component\HttpFoundation\Response;

final class DiscardDraft extends AbstractController
{
    /**
     * @var \Netgen\Layouts\API\Service\LayoutService
     */
    private $layoutService;

    public function __construct(LayoutService $layoutService)
    {
        $this->layoutService = $layoutService;
    }

    /**
     * Discards a layout draft.
     */
    public function __invoke(Layout $layout): Response
    {
        $this->denyAccessUnlessGranted('nglayouts:layout:edit', $layout);

        $this->layoutService->discardDraft($layout);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
