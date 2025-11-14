<?php

declare(strict_types=1);

namespace App\State\Spotify;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\PlatformAuth\Spotify\AuthSpotifyOutput;
use App\Entity\Token;
use App\Entity\User;
use App\Service\Spotify\SpotifyAuthService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProcessorInterface<null, AuthSpotifyOutput>
 */
final readonly class AuthSpotifyProcessor implements ProcessorInterface
{
    public function __construct(
        private SpotifyAuthService $spotifyAuthService,
        private JWTTokenManagerInterface $jwtManager,
        private Security $security,
    ) {
    }

    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): AuthSpotifyOutput
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        $state = $this->jwtManager->create($user);

        $authUrl = $this->spotifyAuthService->getRedirectUri($state);

        $output = new AuthSpotifyOutput();
        $output->authorization_url = $authUrl;
        $output->platform = Token::PLATFORM_SPOTIFY;
        $output->message = 'Visit this URL to authorize the application with Spotify';
        $output->state = $state;

        return $output;
    }
}
