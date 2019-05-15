<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\ParamConverter\LayoutResolver;

use Netgen\BlockManager\API\Service\LayoutResolverService;
use Netgen\BlockManager\API\Values\LayoutResolver\Rule;
use Netgen\BlockManager\API\Values\Value;
use Netgen\Bundle\LayoutsBundle\ParamConverter\ParamConverter;

final class RuleParamConverter extends ParamConverter
{
    /**
     * @var \Netgen\BlockManager\API\Service\LayoutResolverService
     */
    private $layoutResolverService;

    public function __construct(LayoutResolverService $layoutResolverService)
    {
        $this->layoutResolverService = $layoutResolverService;
    }

    public function getSourceAttributeNames(): array
    {
        return ['ruleId'];
    }

    public function getDestinationAttributeName(): string
    {
        return 'rule';
    }

    public function getSupportedClass(): string
    {
        return Rule::class;
    }

    public function loadValue(array $values): Value
    {
        if ($values['status'] === self::STATUS_PUBLISHED) {
            return $this->layoutResolverService->loadRule($values['ruleId']);
        }

        if ($values['status'] === self::STATUS_ARCHIVED) {
            return $this->layoutResolverService->loadRuleArchive($values['ruleId']);
        }

        return $this->layoutResolverService->loadRuleDraft($values['ruleId']);
    }
}
