<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\Layout\Resolver\TargetType;

use Netgen\Layouts\Layout\Resolver\TargetType\RequestUri;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

final class RequestUriTest extends TestCase
{
    private RequestUri $targetType;

    protected function setUp(): void
    {
        $this->targetType = new RequestUri();
    }

    /**
     * @covers \Netgen\Layouts\Layout\Resolver\TargetType\RequestUri::getType
     */
    public function testGetType(): void
    {
        self::assertSame('request_uri', $this->targetType::getType());
    }

    /**
     * @param mixed $value
     *
     * @covers \Netgen\Layouts\Layout\Resolver\TargetType\RequestUri::getConstraints
     * @dataProvider validationDataProvider
     */
    public function testValidation($value, bool $isValid): void
    {
        $validator = Validation::createValidator();

        $errors = $validator->validate($value, $this->targetType->getConstraints());
        self::assertSame($isValid, $errors->count() === 0);
    }

    /**
     * @covers \Netgen\Layouts\Layout\Resolver\TargetType\RequestUri::provideValue
     */
    public function testProvideValue(): void
    {
        $request = Request::create('/the/answer', Request::METHOD_GET, ['a' => 42]);

        self::assertSame(
            '/the/answer?a=42',
            $this->targetType->provideValue($request),
        );
    }

    public function validationDataProvider(): array
    {
        return [
            ['/some/route?id=42', true],
            ['/', true],
            ['', false],
            [null, false],
        ];
    }
}
