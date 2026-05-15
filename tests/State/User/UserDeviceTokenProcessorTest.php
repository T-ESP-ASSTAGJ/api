<?php

declare(strict_types=1);

namespace App\Tests\State\User;

use ApiPlatform\Metadata\Post;
use App\ApiResource\User\UserDeviceTokenInput;
use App\Entity\User;
use App\State\User\UserDeviceTokenProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserDeviceTokenProcessorTest extends TestCase
{
    public function testSetsDeviceTokenAndReturnsOk(): void
    {
        $user = $this->createMock(User::class);
        $user->expects($this->once())->method('setDeviceToken')->with('my-device-token');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($user);
        $em->expects($this->once())->method('flush');

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $processor = new UserDeviceTokenProcessor($em, $security);

        $input = new UserDeviceTokenInput();
        $input->deviceToken = 'my-device-token';

        $result = $processor->process($input, new Post());

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertSame('{"status":"ok"}', $result->getContent());
    }
}
