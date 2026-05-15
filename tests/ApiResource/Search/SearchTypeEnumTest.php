<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Search;

use App\ApiResource\Search\SearchTypeEnum;
use PHPUnit\Framework\TestCase;

class SearchTypeEnumTest extends TestCase
{
    public function testCaseValues(): void
    {
        $this->assertSame('users', SearchTypeEnum::Users->value);
        $this->assertSame('posts', SearchTypeEnum::Posts->value);
        $this->assertSame('tracks', SearchTypeEnum::Tracks->value);
        $this->assertSame('artists', SearchTypeEnum::Artists->value);
    }

    public function testFromValue(): void
    {
        $this->assertSame(SearchTypeEnum::Users, SearchTypeEnum::from('users'));
        $this->assertSame(SearchTypeEnum::Posts, SearchTypeEnum::from('posts'));
        $this->assertSame(SearchTypeEnum::Tracks, SearchTypeEnum::from('tracks'));
        $this->assertSame(SearchTypeEnum::Artists, SearchTypeEnum::from('artists'));
    }

    public function testTryFromInvalidValue(): void
    {
        $this->assertNull(SearchTypeEnum::tryFrom('invalid'));
    }

    public function testAllCases(): void
    {
        $cases = SearchTypeEnum::cases();

        $this->assertCount(4, $cases);
    }
}
