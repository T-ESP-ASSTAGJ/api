<?php

declare(strict_types=1);

namespace App\Tests\State\User;

use ApiPlatform\Metadata\Get;
use App\Entity\User;
use App\State\User\UserMeProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserMeProviderTest extends TestCase
{
    public function testProvidesCurrentUser(): void
    {
        $user = new User();
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $provider = new UserMeProvider($security);

        $this->assertSame($user, $provider->provide(new Get()));
    }

    public function testThrowsWhenUnauthenticated(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $provider = new UserMeProvider($security);

        $this->expectException(UnauthorizedHttpException::class);
        $provider->provide(new Get());
    }
}
