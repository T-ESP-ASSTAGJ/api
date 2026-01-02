<?php

declare(strict_types=1);

namespace App\Tests\ApiResource\Artist;

use App\ApiResource\Artist\ArtistCreateInput;
use App\ApiResource\Artist\ArtistSourceDto;
use PHPUnit\Framework\TestCase;

class ArtistCreateInputTest extends TestCase
{
    public function testPublicProperties(): void
    {
        $input = new ArtistCreateInput();
        $input->name = 'The Beatles';
        $input->artistSources = [];

        $this->assertSame('The Beatles', $input->name);
        $this->assertSame([], $input->artistSources);
    }

    public function testDefaultArtistSourcesIsEmptyArray(): void
    {
        $input = new ArtistCreateInput();

        $this->assertIsArray($input->artistSources);
        $this->assertCount(0, $input->artistSources);
    }

    public function testCanSetArtistSourcesArray(): void
    {
        $input = new ArtistCreateInput();
        $source1 = new ArtistSourceDto();
        $source2 = new ArtistSourceDto();

        $input->artistSources = [$source1, $source2];

        $this->assertCount(2, $input->artistSources);
        $this->assertSame($source1, $input->artistSources[0]);
        $this->assertSame($source2, $input->artistSources[1]);
    }
}
