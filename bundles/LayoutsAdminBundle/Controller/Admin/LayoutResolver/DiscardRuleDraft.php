<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\Controller\Admin\LayoutResolver;

use Netgen\Bundle\LayoutsBundle\Controller\AbstractController;
use Netgen\Layouts\API\Service\LayoutResolverService;
use Netgen\Layouts\API\Values\LayoutResolver\Rule;
use Netgen\Layouts\View\ViewInterface;

final class DiscardRuleDraft extends AbstractController
{
    /**
     * @var \Netgen\Layouts\API\Service\LayoutResolverService
     */
    private $layoutResolverService;

    public function __construct(LayoutResolverService $layoutResolverService)
    {
        $this->layoutResolverService = $layoutResolverService;
    }

    /**
     * Discards a rule draft.
     */
    public function __invoke(Rule $rule): ViewInterface
    {
        $this->denyAccessUnlessGranted('nglayouts:mapping:edit');

        $this->layoutResolverService->discardDraft($rule);

        $publishedRule = $this->layoutResolverService->loadRule($rule->getId());

        return $this->buildView($publishedRule, ViewInterface::CONTEXT_ADMIN);
    }
}
