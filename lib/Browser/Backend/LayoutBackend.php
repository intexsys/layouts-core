<?php

declare(strict_types=1);

namespace Netgen\Layouts\Browser\Backend;

use Generator;
use Netgen\ContentBrowser\Backend\BackendInterface;
use Netgen\ContentBrowser\Config\Configuration;
use Netgen\ContentBrowser\Exceptions\NotFoundException;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\ContentBrowser\Item\LocationInterface;
use Netgen\Layouts\API\Service\LayoutService;
use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\API\Values\Layout\LayoutList;
use Netgen\Layouts\Browser\Item\Layout\Item;
use Netgen\Layouts\Browser\Item\Layout\RootLocation;
use Netgen\Layouts\Exception\NotFoundException as BaseNotFoundException;
use Ramsey\Uuid\Uuid;

final class LayoutBackend implements BackendInterface
{
    /**
     * @var \Netgen\Layouts\API\Service\LayoutService
     */
    private $layoutService;

    /**
     * @var \Netgen\ContentBrowser\Config\Configuration
     */
    private $config;

    public function __construct(LayoutService $layoutService, Configuration $config)
    {
        $this->layoutService = $layoutService;
        $this->config = $config;
    }

    public function getSections(): iterable
    {
        return [new RootLocation()];
    }

    public function loadLocation($id): LocationInterface
    {
        return new RootLocation();
    }

    public function loadItem($value): ItemInterface
    {
        try {
            $layout = $this->layoutService->loadLayout(Uuid::fromString((string) $value));
        } catch (BaseNotFoundException $e) {
            throw new NotFoundException(
                sprintf('Item with value "%s" not found.', $value),
                0,
                $e
            );
        }

        return $this->buildItem($layout);
    }

    public function getSubLocations(LocationInterface $location): iterable
    {
        return [];
    }

    public function getSubLocationsCount(LocationInterface $location): int
    {
        return 0;
    }

    public function getSubItems(LocationInterface $location, int $offset = 0, int $limit = 25): iterable
    {
        $layouts = $this->includeSharedLayouts() ?
            $this->layoutService->loadAllLayouts(false, $offset, $limit) :
            $this->layoutService->loadLayouts(false, $offset, $limit);

        return iterator_to_array($this->buildItems($layouts));
    }

    public function getSubItemsCount(LocationInterface $location): int
    {
        return $this->includeSharedLayouts() ?
            $this->layoutService->getAllLayoutsCount() :
            $this->layoutService->getLayoutsCount();
    }

    public function search(string $searchText, int $offset = 0, int $limit = 25): iterable
    {
        return [];
    }

    public function searchCount(string $searchText): int
    {
        return 0;
    }

    /**
     * Builds the item from provided layout.
     */
    private function buildItem(Layout $layout): Item
    {
        return new Item($layout);
    }

    /**
     * Builds the items from provided layouts.
     */
    private function buildItems(LayoutList $layouts): Generator
    {
        foreach ($layouts as $layout) {
            yield $this->buildItem($layout);
        }
    }

    /**
     * Returns if the backend should include shared layouts or not.
     */
    private function includeSharedLayouts(): bool
    {
        if (!$this->config->hasParameter('include_shared_layouts')) {
            return false;
        }

        return $this->config->getParameter('include_shared_layouts') === 'true';
    }
}
