<?php

declare(strict_types=1);

namespace App\Tests\State\User;

use ApiPlatform\Metadata\Get;
use App\Entity\User;
use App\Entity\UserParameter;
use App\State\User\UserParameterProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class UserParameterProviderTest extends TestCase
{
    public function testReturnsUserParameters(): void
    {
        $params = new UserParameter();
        $user = $this->createMock(User::class);
        $user->method('getParameters')->willReturn($params);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $provider = new UserParameterProvider($security);

        $this->assertSame($params, $provider->provide(new Get()));
    }

    public function testReturnsNullWhenUnauthenticated(): void
    {
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn(null);

        $provider = new UserParameterProvider($security);

        $this->assertNull($provider->provide(new Get()));
    }
}
