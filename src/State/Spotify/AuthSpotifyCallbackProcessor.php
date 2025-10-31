<?php

declare(strict_types=1);

namespace App\State\Spotify;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\PlatformAuth\Spotify\AuthSpotifyCallbackInput;
use App\ApiResource\PlatformAuth\Spotify\AuthSpotifyCallbackOutput;
use App\Entity\User;
use App\Service\Spotify\SpotifyAuthService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProcessorInterface<AuthSpotifyCallbackInput, AuthSpotifyCallbackOutput>
 */
final readonly class AuthSpotifyCallbackProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly SpotifyAuthService $spotifyAuthService,
        private Security $security,
    ) {
    }

    /**
     * @param AuthSpotifyCallbackInput $data
     * @param Operation|null           $operation
     * @param array<string, mixed>     $uriVariables
     * @param array<string, mixed>     $context
     *
     * @return AuthSpotifyCallbackOutput
     */
    public function process(mixed $data, $operation = null, array $uriVariables = [], array $context = []): mixed
    {
        $output = new AuthSpotifyCallbackOutput();

        /** @var User|null $user */
        $user = $this->security->getUser();
        if (!$user) {
            throw new UnauthorizedHttpException('Bearer', 'User not authenticated.');
        }

        try {
            $token = $this->spotifyAuthService->exchangeCodeForToken($data->code, $user);

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
