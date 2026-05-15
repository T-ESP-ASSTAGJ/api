<?php

declare(strict_types=1);

namespace App\Tests\State\Like;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Like\LikeCreateInput;
use App\Entity\Enum\LikeableTypeEnum;
use App\Entity\Like;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\State\Like\LikeDeleteProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LikeDeleteProcessorTest extends TestCase
{
    /**
     * @var ProcessorInterface<mixed, mixed>&\PHPUnit\Framework\MockObject\MockObject
     */
    private ProcessorInterface $removeProcessor;

    /**
     * @var LikeRepository&\PHPUnit\Framework\MockObject\MockObject
     */
    private LikeRepository $likeRepository;

    /**
     * @var Security&\PHPUnit\Framework\MockObject\MockObject
     */
    private Security $security;

    private LikeDeleteProcessor $processor;

    protected function setUp(): void
    {
        $this->removeProcessor = $this->createMock(ProcessorInterface::class);
        $this->likeRepository = $this->createMock(LikeRepository::class);
        $this->security = $this->createMock(Security::class);
        $this->processor = new LikeDeleteProcessor(
            $this->removeProcessor,
            $this->likeRepository,
            $this->security,
        );
    }

    public function testDeletesLike(): void
    {
        $user = new User();
        $like = new Like();

        $this->security->method('getUser')->willReturn($user);
        $this->likeRepository->method('findOneBy')->willReturn($like);
        $this->removeProcessor->expects($this->once())->method('process')->with($like);

        $input = new LikeCreateInput();
        $input->entityClass = LikeableTypeEnum::Post;
        $input->entityId = 1;

        $this->processor->process($input, new Delete());
    }

    public function testThrowsWhenLikeNotFound(): void
    {
        $user = new User();
        $this->security->method('getUser')->willReturn($user);
        $this->likeRepository->method('findOneBy')->willReturn(null);

        $input = new LikeCreateInput();
        $input->entityClass = LikeableTypeEnum::Post;
        $input->entityId = 999;

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($input, new Delete());
    }
}
