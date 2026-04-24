<?php

declare(strict_types=1);

namespace App\State\User;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Entity\UserParameter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<UserParameter, UserParameter>
 */
final readonly class UserParameterProcessor implements ProcessorInterface
{
    public function __construct(
        /**
         * @var ProcessorInterface<UserParameter, UserParameter>
         */
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private Security $security,
    ) {
    }

    /**
     * @param UserParameter $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserParameter
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $data->setUser($user);

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
