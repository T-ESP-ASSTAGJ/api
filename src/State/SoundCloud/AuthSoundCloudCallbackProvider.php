<?php

declare(strict_types=1);

namespace App\State\SoundCloud;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\PlatformAuth\SoundCloud\AuthSoundCloudCallbackOutput;
use App\Entity\User;
use App\Service\SoundCloud\SoundCloudAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @implements ProviderInterface<AuthSoundCloudCallbackOutput>
 */
final readonly class AuthSoundCloudCallbackProvider implements ProviderInterface
{
    public function __construct(
        private SoundCloudAuthService $soundCloudAuthService,
        private JWTEncoderInterface $jwtEncoder,
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AuthSoundCloudCallbackOutput
    {
        $output = new AuthSoundCloudCallbackOutput();

        $request = $this->requestStack->getMainRequest();
        if (!$request) {
            throw new \RuntimeException('Cannot access the main request.');
        }

        $code = $request->query->get('code');
        $stateToken = $request->query->get('state');

        if (!$code) {
            throw new BadRequestHttpException('Missing code parameter from SoundCloud.');
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
            $token = $this->soundCloudAuthService->exchangeCodeForToken($code, $user);

            $output->success = true;
            $output->expires_at = $token->getExpiresAt()->format('c');
            $output->message = 'SoundCloud authorization completed successfully';
        } catch (\Exception $e) {
            $output->success = false;
            $output->error = 'Failed to complete SoundCloud authorization';
            $output->details = $e->getMessage();
        }

        return $output;
    }
}