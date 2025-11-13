<?php

declare(strict_types=1);

namespace App\State\Spotify;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\PlatformAuth\Spotify\AuthSpotifyCallbackOutput;
use App\Entity\User;
use App\Service\Spotify\SpotifyAuthService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProviderInterface<AuthSpotifyCallbackOutput>
 */
final readonly class AuthSpotifyCallbackProvider implements ProviderInterface
{
    public function __construct(
        private SpotifyAuthService $spotifyAuthService,
        private Security $security,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AuthSpotifyCallbackOutput
    {
        $output = new AuthSpotifyCallbackOutput();

        /** @var User|null $user */
        $user = $this->security->getUser();
        if (!$user) {
            throw new UnauthorizedHttpException('Bearer', 'User not authenticated.');
        }

        $code = $uriVariables['state'];

        try {
            $token = $this->spotifyAuthService->exchangeCodeForToken($code, $user);

            $output->success = true;
            $output->expires_at = $token->getExpiresAt()->format('c');
            $output->message = 'Spotify authorization completed successfully';
        } catch (\Exception $e) {
            $output->success = false;
            $output->error = 'Failed to complete Spotify authorization';
            $output->details = $e->getMessage();
        }

        return $output;
    }
}
