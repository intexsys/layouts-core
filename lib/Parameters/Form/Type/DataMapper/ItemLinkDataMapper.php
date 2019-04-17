<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Parameters\Form\Type\DataMapper;

use Symfony\Component\Form\DataMapperInterface;

/**
 * Mapper used to convert to and from the "link" to an item in "value_type://value"
 * format to the Symfony form structure.
 */
final class ItemLinkDataMapper implements DataMapperInterface
{
    public function mapDataToForms($viewData, $forms): void
    {
        if (!is_string($viewData)) {
            return;
        }

        $parsedData = parse_url($viewData);
        if (is_array($parsedData) && ($parsedData['scheme'] ?? '') !== '' && isset($parsedData['host'])) {
            $forms = iterator_to_array($forms);
            $forms['item_value']->setData($parsedData['host']);
            $forms['item_type']->setData(str_replace('-', '_', $parsedData['scheme']));
        }
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        $forms = iterator_to_array($forms);

        $itemValue = $forms['item_value']->getData() ?? '';
        $itemType = $forms['item_type']->getData() ?? '';

        $viewData = null;
        if ($itemValue !== '' && $itemType !== '') {
            $viewData = str_replace('_', '-', $itemType) . '://' . $itemValue;
        }
    }
}
