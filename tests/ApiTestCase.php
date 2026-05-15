<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class ApiTestCase extends \ApiPlatform\Symfony\Bundle\Test\ApiTestCase
{
    use Factories;
    use ResetDatabase;

    protected static ?bool $alwaysBootKernel = true;

    protected Client $client;

    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em = $em;
    }

    protected function createAuthenticatedClient(User $user): HttpClientInterface
    {
        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($user);

        return static::createClient([], [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}
