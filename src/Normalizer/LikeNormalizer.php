<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Like;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class LikeNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'LIKE_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws ExceptionInterface
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function normalize($data, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        dump($context);

        $entityClass = $data->getEntityClass();
        $entityId = $data->getEntityId();

        $entity = $this->entityManager->find($entityClass, $entityId);

        $normalized = $entity
            ? $this->normalizer->normalize($entity, $format, $context)
            : null;

        unset($normalized['entityId'], $normalized['entityClass']);

        return $normalized;
    }


    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Like;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Like::class => true,
        ];
    }
}
