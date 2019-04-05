<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Item;

use Netgen\BlockManager\Exception\Item\ItemException;

final class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * @var \Netgen\BlockManager\Item\ValueUrlGeneratorInterface[]
     */
    private $valueUrlGenerators;

    /**
     * @param \Netgen\BlockManager\Item\ValueUrlGeneratorInterface[] $valueUrlGenerators
     */
    public function __construct(array $valueUrlGenerators)
    {
        $this->valueUrlGenerators = array_filter(
            $valueUrlGenerators,
            static function (ValueUrlGeneratorInterface $valueUrlGenerator): bool {
                return true;
            }
        );
    }

    public function generate(CmsItemInterface $item): string
    {
        $object = $item->getObject();
        if ($item instanceof NullCmsItem || $object === null) {
            return '';
        }

        $valueType = $item->getValueType();

        if (!isset($this->valueUrlGenerators[$valueType])) {
            throw ItemException::noValueType($valueType);
        }

        return $this->valueUrlGenerators[$valueType]->generate($object) ?? '';
    }
}
