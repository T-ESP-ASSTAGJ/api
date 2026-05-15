<?php

declare(strict_types=1);

namespace App\Tests\State\Search;

use ApiPlatform\Metadata\GetCollection;
use App\Entity\Post;
use App\Entity\User;
use App\Factory\PostFactory;
use App\Factory\TrackFactory;
use App\Factory\UserFactory;
use App\State\Search\SearchProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Zenstruck\Foundry\Test\ResetDatabase;

class SearchProviderTest extends KernelTestCase
{
    use ResetDatabase;

    private SearchProvider $provider;

    private GetCollection $operation;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->provider = self::getContainer()->get(SearchProvider::class);
        $this->operation = new GetCollection(uriTemplate: '/search');
    }

    public function testSearchPostsByQuery(): void
    {
        UserFactory::createOne();
        PostFactory::createOne(['caption' => 'musique jazz incroyable']);
        PostFactory::createOne(['caption' => 'photo de vacances']);

        $results = $this->provider->provide($this->operation, [], [
            'filters' => ['query' => 'jazz', 'type' => 'posts'],
        ]);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(Post::class, $results[0]);
    }

    public function testSearchUsersByQuery(): void
    {
        UserFactory::createOne(['username' => 'johndoe']);
        UserFactory::createOne(['username' => 'janedoe']);
        UserFactory::createOne(['username' => 'randomuser']);

        $results = $this->provider->provide($this->operation, [], [
            'filters' => ['query' => 'doe', 'type' => 'users'],
        ]);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(User::class, $results[0]);
    }

    public function testDefaultTypeIsUsers(): void
    {
        UserFactory::createOne(['username' => 'testuser']);
        PostFactory::createOne(['caption' => 'test caption']);

        $results = $this->provider->provide($this->operation, [], [
            'filters' => ['query' => 'test'],
        ]);

        $this->assertNotEmpty($results);
        $this->assertInstanceOf(User::class, $results[0]);
    }

    public function testEmptyQueryThrowsBadRequestException(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->provider->provide($this->operation, [], [
            'filters' => ['query' => ''],
        ]);
    }

    public function testMissingQueryThrowsBadRequestException(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->provider->provide($this->operation, [], ['filters' => []]);
    }

    public function testSearchReturnsEmptyArrayWhenNoMatch(): void
    {
        UserFactory::createOne();
        PostFactory::createOne(['caption' => 'contenu sans rapport']);

        $results = $this->provider->provide($this->operation, [], [
            'filters' => ['query' => 'xyzabc123', 'type' => 'posts'],
        ]);

        $this->assertCount(0, $results);
    }

    public function testSearchTracksByTitle(): void
    {
        UserFactory::createOne();
        PostFactory::createOne(['track' => TrackFactory::createOne(['title' => 'bohemian rhapsody'])]);
        PostFactory::createOne(['track' => TrackFactory::createOne(['title' => 'hotel california'])]);

        $results = $this->provider->provide($this->operation, [], [
            'filters' => ['query' => 'bohemian', 'type' => 'tracks'],
        ]);

        $this->assertCount(1, $results);
        $this->assertInstanceOf(Post::class, $results[0]);
    }

    public function testSearchTracksReturnsEmptyWhenNoMatch(): void
    {
        UserFactory::createOne();
        PostFactory::createOne(['track' => TrackFactory::createOne(['title' => 'bohemian rhapsody'])]);

        $results = $this->provider->provide($this->operation, [], [
            'filters' => ['query' => 'xyzabc123', 'type' => 'tracks'],
        ]);

        $this->assertCount(0, $results);
    }

    public function testSearchArtistsByName(): void
    {
        UserFactory::createOne();
        PostFactory::createOne(['track' => TrackFactory::createOne(['artistName' => 'pink floyd'])]);
        PostFactory::createOne(['track' => TrackFactory::createOne(['artistName' => 'pink panther'])]);
        PostFactory::createOne(['track' => TrackFactory::createOne(['artistName' => 'radiohead'])]);

        $results = $this->provider->provide($this->operation, [], [
            'filters' => ['query' => 'pink', 'type' => 'artists'],
        ]);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(Post::class, $results[0]);
    }

    public function testSearchArtistsReturnsEmptyWhenNoMatch(): void
    {
        UserFactory::createOne();
        PostFactory::createOne(['track' => TrackFactory::createOne(['artistName' => 'pink floyd'])]);

        $results = $this->provider->provide($this->operation, [], [
            'filters' => ['query' => 'xyzabc123', 'type' => 'artists'],
        ]);

        $this->assertCount(0, $results);
    }
}
