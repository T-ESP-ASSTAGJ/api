<?php

declare(strict_types=1);

namespace App\Tests\Trait;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait AuthenticationTrait
{
    private function authenticateUser(User $user): void
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        self::getContainer()->get('security.token_storage')->setToken($token);
    }
}
