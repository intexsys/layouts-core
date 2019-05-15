<?php

declare(strict_types=1);

namespace Netgen\Layouts\Browser\Item\ColumnProvider\Layout;

use Netgen\ContentBrowser\Item\ColumnProvider\ColumnValueProviderInterface;
use Netgen\ContentBrowser\Item\ItemInterface;
use Netgen\Layouts\Browser\Item\Layout\LayoutInterface;

final class Created implements ColumnValueProviderInterface
{
    /**
     * @var string
     */
    private $dateFormat;

    public function __construct(string $dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    public function getValue(ItemInterface $item): ?string
    {
        if (!$item instanceof LayoutInterface) {
            return null;
        }

        return $item->getLayout()->getCreated()->format(
            $this->dateFormat
        );
    }
}
