<?php

declare(strict_types=1);

namespace App\Tests\State\User;

use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Entity\UserParameter;
use App\State\User\UserParameterProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class UserParameterProcessorTest extends TestCase
{
    public function testSetsUserAndDelegatesToPersistProcessor(): void
    {
        $user = new User();
        $param = new UserParameter();

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $persistProcessor = $this->createMock(ProcessorInterface::class);
        $persistProcessor->expects($this->once())
            ->method('process')
            ->with($param, $this->anything())
            ->willReturn($param)
        ;

        $processor = new UserParameterProcessor($persistProcessor, $security);

        $result = $processor->process($param, new Post());

        $this->assertSame($param, $result);
        $this->assertSame($user, $param->getUser());
    }
}
