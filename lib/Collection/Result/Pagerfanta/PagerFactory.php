<?php

declare(strict_types=1);

namespace Netgen\Layouts\Collection\Result\Pagerfanta;

use Netgen\Layouts\API\Values\Collection\Collection;
use Netgen\Layouts\Collection\Result\ResultBuilderInterface;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use function is_int;

final class PagerFactory
{
    private ResultBuilderInterface $resultBuilder;

    private int $maxLimit;

    public function __construct(ResultBuilderInterface $resultBuilder, int $maxLimit)
    {
        $this->resultBuilder = $resultBuilder;
        $this->maxLimit = $maxLimit;
    }

    /**
     * Builds and returns the Pagerfanta pager for provided collection.
     *
     * The pager starting page will be set to $startPage.
     */
    public function getPager(Collection $collection, int $startPage, ?int $maxPages = null, int $flags = 0): Pagerfanta
    {
        $maxTotalCount = null;
        if (is_int($maxPages) && $maxPages > 0) {
            $maxTotalCount = $maxPages * $this->getMaxPerPage($collection);
        }

        $pagerAdapter = new ResultBuilderAdapter(
            $this->resultBuilder,
            $collection,
            $collection->getOffset(),
            $maxTotalCount,
            $flags
        );

        return $this->buildPager($pagerAdapter, $collection, $startPage);
    }

    /**
     * Builds the pager from provided adapter.
     */
    private function buildPager(AdapterInterface $adapter, Collection $collection, int $startPage): Pagerfanta
    {
        $pager = new Pagerfanta($adapter);

        $pager->setNormalizeOutOfRangePages(true);
        $pager->setMaxPerPage($this->getMaxPerPage($collection));
        $pager->setCurrentPage($startPage > 0 ? $startPage : 1);

        return $pager;
    }

    /**
     * Returns the maximum number of items per page for the provided collection,
     * while taking into account the injected maximum number of items.
     */
    private function getMaxPerPage(Collection $collection): int
    {
        $limit = $collection->getLimit() ?? 0;

        return $limit > 0 && $limit < $this->maxLimit ? $limit : $this->maxLimit;
    }
}
