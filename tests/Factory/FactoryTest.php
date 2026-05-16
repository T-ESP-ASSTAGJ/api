<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\Artist;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Report;
use App\Entity\Track;
use App\Entity\User;
use App\Entity\UserParameter;
use App\Entity\VerificationUser;
use App\Factory\ArtistFactory;
use App\Factory\CommentFactory;
use App\Factory\PostFactory;
use App\Factory\ReportFactory;
use App\Factory\TrackFactory;
use App\Factory\UserFactory;
use App\Factory\UserParameterFactory;
use App\Factory\VerificationUserFactory;
use PHPUnit\Framework\TestCase;
use Zenstruck\Foundry\Test\Factories;

class FactoryTest extends TestCase
{
    use Factories;

    public function testArtistFactory(): void
    {
        $this->assertSame(Artist::class, ArtistFactory::class());
        $this->assertInstanceOf(Artist::class, ArtistFactory::new()->create());
    }

    public function testTrackFactory(): void
    {
        $this->assertSame(Track::class, TrackFactory::class());
        $this->assertInstanceOf(Track::class, TrackFactory::new()->create());
    }

    public function testUserFactory(): void
    {
        $this->assertSame(User::class, UserFactory::class());
        $this->assertInstanceOf(User::class, UserFactory::new()->create());
    }

    public function testUserParameterFactory(): void
    {
        $this->assertSame(UserParameter::class, UserParameterFactory::class());
        $this->assertInstanceOf(UserParameter::class, UserParameterFactory::new()->create());
    }

    public function testVerificationUserFactory(): void
    {
        $this->assertSame(VerificationUser::class, VerificationUserFactory::class());
        $this->assertInstanceOf(VerificationUser::class, VerificationUserFactory::new()->create());
    }

    public function testPostFactory(): void
    {
        $this->assertSame(Post::class, PostFactory::class());
        $this->assertInstanceOf(Post::class, PostFactory::new()->create());
    }

    public function testReportFactory(): void
    {
        $this->assertSame(Report::class, ReportFactory::class());
        $this->assertInstanceOf(Report::class, ReportFactory::new()->create());
    }
}
