<?php

declare(strict_types=1);

namespace App\State\Spotify;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\PlatformAuth\Spotify\AuthSpotifyCallbackInput;
use App\DTO\PlatformAuth\Spotify\AuthSpotifyCallbackOutput;
use App\Entity\Token;
use App\Service\Spotify\SpotifyAuthService;

/**
 * @implements ProcessorInterface<AuthSpotifyCallbackInput, AuthSpotifyCallbackOutput>
 */
final readonly class AuthSpotifyCallbackProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly SpotifyAuthService $spotifyAuthService,
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
        $output->platform = Token::PLATFORM_SPOTIFY;

        // TODO: Handle user authentication - for now, we'll need to get the user from somewhere
        $user = null;

        if (!$user) {
            $output->success = false;
            $output->error = 'User must be authenticated';

            return $output;
        }

        if ($data->error) {
            $output->success = false;
            $output->error = 'Authorization denied: '.$data->error;

            return $output;
        }

        if (!$data->code) {
            $output->success = false;
            $output->error = 'Authorization code not provided';

            return $output;
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
