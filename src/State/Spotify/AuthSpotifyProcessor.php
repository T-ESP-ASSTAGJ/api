<?php

declare(strict_types=1);

namespace App\State\Spotify;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\PlatformAuth\Spotify\AuthSpotifyOutput;
use App\Entity\Token;
use App\Service\Spotify\SpotifyAuthService;
use Random\RandomException;

/**
 * @implements ProcessorInterface<null, AuthSpotifyOutput>
 */
final readonly class AuthSpotifyProcessor implements ProcessorInterface
{
    public function __construct(
        private SpotifyAuthService $spotifyAuthService,
    ) {
    }

    /**
     * @param null                 $data
     * @param Operation|null       $operation
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @throws RandomException
     */
    public function process(mixed $data, $operation = null, array $uriVariables = [], array $context = []): AuthSpotifyOutput
    {
        $authUrl = $this->spotifyAuthService->getAuthorizationUrl([
            'user-read-private',
            'user-read-email',
            'playlist-read-private',
            'playlist-read-collaborative',
            'user-library-read',
        ]);

        $output = new AuthSpotifyOutput();
        $output->authorization_url = $authUrl;
        $output->platform = Token::PLATFORM_SPOTIFY;
        $output->message = 'Visit this URL to authorize the application with Spotify';

        return $output;
    }
}
