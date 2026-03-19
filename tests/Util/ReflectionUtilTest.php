<?php

declare(strict_types=1);

namespace App\Tests\Util;

use App\Util\ReflectionUtil;
use PHPUnit\Framework\TestCase;

class MockObject
{
    /** @phpstan-ignore-next-line -- Property is read via Reflection in tests */
    private mixed $privateProperty = 'initial_private';

    protected mixed $protectedProperty = 'initial_protected';

    public mixed $publicProperty = 'initial_public';
}

class ReflectionUtilTest extends TestCase
{
    private MockObject $mock;

    protected function setUp(): void
    {
        $this->mock = new MockObject();
    }

    /**
     * @dataProvider propertyDataProvider
     */
    public function testGetPropertyValueRetrievesCorrectValues(string $propertyName, mixed $expectedValue): void
    {
        $actual = ReflectionUtil::getPropertyValue($this->mock, $propertyName);

        $this->assertSame($expectedValue, $actual);
    }

    /**
     * @dataProvider propertyDataProvider
     */
    public function testSetPropertyValueModifiesValues(string $propertyName): void
    {
        $newValue = 'new_value_'.bin2hex(random_bytes(4));

        ReflectionUtil::setPropertyValue($this->mock, $propertyName, $newValue);

        $this->assertSame($newValue, ReflectionUtil::getPropertyValue($this->mock, $propertyName));
    }

    public function testGetPropertyValueThrowsExceptionOnInvalidProperty(): void
    {
        $this->expectException(\ReflectionException::class);
        ReflectionUtil::getPropertyValue($this->mock, 'nonExistentProperty');
    }

    public function testSetPropertyValueThrowsExceptionOnInvalidProperty(): void
    {
        $this->expectException(\ReflectionException::class);
        ReflectionUtil::setPropertyValue($this->mock, 'nonExistentProperty', 'foo');
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public static function propertyDataProvider(): array
    {
        return [
            'private property' => ['privateProperty', 'initial_private'],
            'protected property' => ['protectedProperty', 'initial_protected'],
            'public property' => ['publicProperty', 'initial_public'],
        ];
    }
}