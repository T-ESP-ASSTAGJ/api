<?php

declare(strict_types=1);

namespace App\State\Mercure;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Mercure\MercureTokenOutput;
use App\Entity\User;
use App\Repository\ConversationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProviderInterface<MercureTokenOutput>
 */
final readonly class MercureTokenProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
        private ConversationRepository $conversationRepository,
        private string $mercureSecret,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MercureTokenOutput
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        $conversations = $this->conversationRepository->findByUser($user);

        $topics = array_map(
            fn ($conversation) => '/conversations/'.$conversation->getId(),
            $conversations
        );

        $output = new MercureTokenOutput();
        $output->token = $this->generateMercureToken($topics);
        $output->topics = $topics;

        return $output;
    }

    /**
     * @param array<string> $subscribeTopics
     */
    private function generateMercureToken(array $subscribeTopics): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));

        $payload = $this->base64UrlEncode(json_encode([
            'mercure' => [
                'subscribe' => $subscribeTopics,
            ],
            'exp' => time() + 3600,
        ], JSON_THROW_ON_ERROR));

        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', $header.'.'.$payload, $this->mercureSecret, true)
        );

        return $header.'.'.$payload.'.'.$signature;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
