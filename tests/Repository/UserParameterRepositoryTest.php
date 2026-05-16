<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\UserParameterRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserParameterRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testRepositoryIsInstantiable(): void
    {
        $repo = static::getContainer()->get(UserParameterRepository::class);

        $this->assertInstanceOf(UserParameterRepository::class, $repo);
    }
}
