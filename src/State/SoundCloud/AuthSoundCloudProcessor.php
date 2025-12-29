<?php

declare(strict_types=1);

namespace App\State\SoundCloud;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\PlatformAuth\SoundCloud\AuthSoundCloudOutput;
use App\Entity\Token;
use App\Entity\User;
use App\Service\SoundCloud\SoundCloudAuthService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProcessorInterface<null, AuthSoundCloudOutput>
 */
final readonly class AuthSoundCloudProcessor implements ProcessorInterface
{
    public function __construct(
        private SoundCloudAuthService $soundCloudAuthService,
        private JWTTokenManagerInterface $jwtManager,
        private Security $security,
    ) {
    }

    public function process(mixed $data, ?Operation $operation = null, array $uriVariables = [], array $context = []): AuthSoundCloudOutput
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (null === $user) {
            throw new UnauthorizedHttpException('Bearer', 'Authentication required');
        }

        $state = $this->jwtManager->create($user);

        $authUrl = $this->soundCloudAuthService->getRedirectUri($state);

        $output = new AuthSoundCloudOutput();
        $output->authorization_url = $authUrl;
        $output->platform = Token::PLATFORM_SOUNDCLOUD;
        $output->message = 'Visit this URL to authorize the application with SoundCloud';

        return $output;
    }
}