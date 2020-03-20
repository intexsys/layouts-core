<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Templating\Twig\Runtime;

use IntlDateFormatter;
use IntlTimeZone;
use Locale;
use Netgen\Layouts\API\Service\LayoutService;
use Netgen\Layouts\Exception\Item\ItemException;
use Netgen\Layouts\Exception\RuntimeException;
use Netgen\Layouts\Item\CmsItemInterface;
use Netgen\Layouts\Item\Registry\ValueTypeRegistry;
use Netgen\Layouts\Utils\BackwardsCompatibility\Locales;
use Netgen\Layouts\Utils\FlagGenerator;
use Ramsey\Uuid\Uuid;
use Throwable;
use Twig\Environment;
use function twig_date_converter;

final class HelpersRuntime
{
    /**
     * @var \Netgen\Layouts\API\Service\LayoutService
     */
    private $layoutService;

    /**
     * @var \Netgen\Layouts\Item\Registry\ValueTypeRegistry
     */
    private $valueTypeRegistry;

    public function __construct(LayoutService $layoutService, ValueTypeRegistry $valueTypeRegistry)
    {
        $this->layoutService = $layoutService;
        $this->valueTypeRegistry = $valueTypeRegistry;
    }

    /**
     * Returns the locale name in specified locale.
     *
     * If $displayLocale is specified, name translated in that locale will be returned.
     */
    public function getLocaleName(string $locale, ?string $displayLocale = null): ?string
    {
        return Locales::getName($locale, $displayLocale);
    }

    /**
     * Returns the layout name for specified layout ID.
     */
    public function getLayoutName(string $layoutId): string
    {
        try {
            $layout = $this->layoutService->loadLayout(Uuid::fromString($layoutId));

            return $layout->getName();
        } catch (Throwable $t) {
            return '';
        }
    }

    /**
     * Returns the the name of the value type that the specified item wraps.
     */
    public function getValueTypeName(CmsItemInterface $cmsItem): string
    {
        try {
            return $this->valueTypeRegistry->getValueType($cmsItem->getValueType())->getName();
        } catch (ItemException $t) {
            return '';
        }
    }

    /**
     * Returns the country flag as an emoji string for provided country code.
     *
     * If the flag cannot be generated, the country code is returned as is.
     *
     * @param string $countryCode
     */
    public function getCountryFlag(string $countryCode): string
    {
        try {
            return FlagGenerator::fromCountryCode($countryCode);
        } catch (Throwable $t) {
            return $countryCode;
        }
    }

    /**
     * @param \DateTimeInterface|string $dateTime
     */
    public function formatDateTime(Environment $twig, $dateTime, string $dateFormat = 'medium', string $timeFormat = 'medium'): string
    {
        $dateTime = twig_date_converter($twig, $dateTime);

        $formatValues = [
            'none' => IntlDateFormatter::NONE,
            'short' => IntlDateFormatter::SHORT,
            'medium' => IntlDateFormatter::MEDIUM,
            'long' => IntlDateFormatter::LONG,
            'full' => IntlDateFormatter::FULL,
        ];

        $formatter = IntlDateFormatter::create(
            Locale::getDefault(),
            $formatValues[$dateFormat],
            $formatValues[$timeFormat],
            IntlTimeZone::createTimeZone($dateTime->getTimezone()->getName())
        );

        if (!$formatter instanceof IntlDateFormatter) {
            throw new RuntimeException('Unable to format the given date.');
        }

        return $formatter->format($dateTime->getTimestamp());
    }
}
