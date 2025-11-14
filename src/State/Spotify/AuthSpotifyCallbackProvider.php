<?php

declare(strict_types=1);

namespace App\State\Spotify;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\PlatformAuth\Spotify\AuthSpotifyCallbackOutput;
use App\Entity\User;
use App\Service\Spotify\SpotifyAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProviderInterface<AuthSpotifyCallbackOutput>
 */
final readonly class AuthSpotifyCallbackProvider implements ProviderInterface
{
    public function __construct(
        private SpotifyAuthService $spotifyAuthService,
        private JWTEncoderInterface $jwtEncoder,
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AuthSpotifyCallbackOutput
    {
        $output = new AuthSpotifyCallbackOutput();

        $request = $this->requestStack->getMainRequest();
        if (!$request) {
            throw new \RuntimeException('Cannot access the main request.');
        }

        $code = $request->query->get('code');
        $stateToken = $request->query->get('state');

        if (!$code) {
            throw new BadRequestHttpException('Missing code parameter from Spotify.');
        }
        if (!$stateToken) {
            throw new BadRequestHttpException('Missing state parameter.');
        }

        try {
            $payload = $this->jwtEncoder->decode($stateToken);
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $payload['email']]);
        } catch (\Exception) {
            throw new UnauthorizedHttpException('Bearer', 'Invalid state token.');
        }

        if (!$user) {
            throw new UnauthorizedHttpException('Bearer', 'User not found.');
        }

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
