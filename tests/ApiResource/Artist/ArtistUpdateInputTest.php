<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Artist;

use App\ApiResource\Artist\ArtistSourceDto;
use App\ApiResource\Artist\ArtistUpdateInput;
use PHPUnit\Framework\TestCase;

class ArtistUpdateInputTest extends TestCase
{
    public function testDefaultValuesAreNull(): void
    {
        $input = new ArtistUpdateInput();

        $this->assertNull($input->name);
        $this->assertNull($input->artistSources);
    }

    public function testCanSetName(): void
    {
        $input = new ArtistUpdateInput();
        $input->name = 'The Beatles';

        $this->assertSame('The Beatles', $input->name);
    }

    public function testCanSetArtistSources(): void
    {
        $input = new ArtistUpdateInput();
        $source = new ArtistSourceDto();
        $input->artistSources = [$source];

        $this->assertCount(1, $input->artistSources);
        $this->assertSame($source, $input->artistSources[0]);
    }

    public function testCanSetFieldsToNull(): void
    {
        $input = new ArtistUpdateInput();
        $input->name = null;
        $input->artistSources = null;

        $this->assertNull($input->name);
        $this->assertNull($input->artistSources);
    }
}
