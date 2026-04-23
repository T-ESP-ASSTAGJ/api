<?php

declare(strict_types=1);

namespace App\State\Report;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Report\ReportCreateInput;
use App\Entity\Report;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<ReportCreateInput, void>
 */
final readonly class ReportCreateProcessor implements ProcessorInterface
{
    public function __construct(
        /**
         * @var ProcessorInterface<Report, void>
         */
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $em,
        private Security $security,
    ) {
    }

    /**
     * @param ReportCreateInput $data
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): void
    {
        if (!$data instanceof ReportCreateInput) {
            return;
        }

        $entityClass = $data->entityClass->toEntityClass();
        $entity = $this->em->getRepository($entityClass)->find($data->entityId);

        if (!$entity) {
            throw new NotFoundHttpException(\sprintf('Entity %s with id %d not found.', $entityClass, $data->entityId));
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        $report = new Report();
        $report
            ->setUser($user)
            ->setEntityId($data->entityId)
            ->setEntityClass($data->entityClass)
            ->setReason($data->reason)
            ->setMessage($data->message)
        ;

        try {
            $this->persistProcessor->process($report, $operation, $uriVariables, $context);
        } catch (\Throwable) {
            throw new UnprocessableEntityHttpException('Vous avez déjà signalé ce contenu.');
        }
    }
}
