<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Search;

use App\ApiResource\Search\Search;
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
{
    public function testInstantiation(): void
    {
        $search = new Search();

        $this->assertInstanceOf(Search::class, $search);
    }
}
