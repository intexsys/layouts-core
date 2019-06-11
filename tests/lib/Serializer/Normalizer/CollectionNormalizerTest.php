<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Serializer\Normalizer;

use Netgen\Layouts\API\Values\Collection\Collection;
use Netgen\Layouts\API\Values\Collection\Query;
use Netgen\Layouts\Serializer\Normalizer\CollectionNormalizer;
use Netgen\Layouts\Serializer\Values\Value;
use Netgen\Layouts\Tests\API\Stubs\Value as APIValue;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class CollectionNormalizerTest extends TestCase
{
    /**
     * @var \Netgen\Layouts\Serializer\Normalizer\CollectionNormalizer
     */
    private $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new CollectionNormalizer();
    }

    /**
     * @covers \Netgen\Layouts\Serializer\Normalizer\CollectionNormalizer::normalize
     */
    public function testNormalize(): void
    {
        $collection = Collection::fromArray(
            [
                'id' => Uuid::uuid4(),
                'query' => new Query(),
                'isTranslatable' => true,
                'alwaysAvailable' => true,
                'availableLocales' => ['en'],
                'mainLocale' => 'en',
            ]
        );

        self::assertSame(
            [
                'id' => $collection->getId()->toString(),
                'type' => Collection::TYPE_DYNAMIC,
                'is_translatable' => $collection->isTranslatable(),
                'main_locale' => $collection->getMainLocale(),
                'always_available' => $collection->isAlwaysAvailable(),
                'available_locales' => $collection->getAvailableLocales(),
            ],
            $this->normalizer->normalize(new Value($collection))
        );
    }

    /**
     * @param mixed $data
     * @param bool $expected
     *
     * @covers \Netgen\Layouts\Serializer\Normalizer\CollectionNormalizer::supportsNormalization
     * @dataProvider supportsNormalizationProvider
     */
    public function testSupportsNormalization($data, bool $expected): void
    {
        self::assertSame($expected, $this->normalizer->supportsNormalization($data));
    }

    public function supportsNormalizationProvider(): array
    {
        return [
            [null, false],
            [true, false],
            [false, false],
            ['block', false],
            [[], false],
            [42, false],
            [42.12, false],
            [new APIValue(), false],
            [new Collection(), false],
            [new Value(new APIValue()), false],
            [new Value(new Collection()), true],
        ];
    }
}
