<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\TokenRepository;
use App\Service\Spotify\SpotifyAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth', name: 'auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly TokenRepository $tokenRepository,
        private readonly SpotifyAuthService $spotifyAuthService
    ) {}

    #[Route('/spotify/authorize', name: 'spotify_authorize', methods: ['GET'])]
    public function authorizeSpotify(): JsonResponse
    {
        $authUrl = $this->spotifyAuthService->getAuthorizationUrl([
            'user-read-private',
            'user-read-email',
            'playlist-read-private',
            'playlist-read-collaborative',
            'user-library-read',
        ]);

        return new JsonResponse([
            'authorization_url' => $authUrl,
            'platform' => Token::PLATFORM_SPOTIFY,
            'message' => 'Visit this URL to authorize the application with Spotify',
        ]);
    }

    #[Route('/spotify/callback', name: 'spotify_callback', methods: ['GET'])]
    public function callbackSpotify(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'User must be authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $code = $request->query->get('code');
        $error = $request->query->get('error');

        if ($error) {
            return new JsonResponse([
                'error' => 'Authorization denied: ' . $error,
                'platform' => Token::PLATFORM_SPOTIFY,
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$code) {
            return new JsonResponse([
                'error' => 'Authorization code not provided',
                'platform' => Token::PLATFORM_SPOTIFY,
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $token = $this->spotifyAuthService->exchangeCodeForToken($code, $user);

            return new JsonResponse([
                'success' => true,
                'platform' => $token->getPlatform(),
                'expires_at' => $token->getExpiresAt()->format('c'),
                'message' => 'Spotify authorization completed successfully',
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to complete Spotify authorization',
                'details' => $e->getMessage(),
                'platform' => Token::PLATFORM_SPOTIFY,
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/tokens', name: 'tokens', methods: ['GET'])]
    public function getUserTokens(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'User must be authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $tokens = $this->tokenRepository->findAllByUser($user);
        $tokenData = [];

        foreach ($tokens as $token) {
            $tokenData[] = [
                'platform' => $token->getPlatform(),
                'expires_at' => $token->getExpiresAt()->format('c'),
                'is_expired' => $token->isExpired(),
                'scopes' => $token->getScopes(),
            ];
        }

        return new JsonResponse([
            'tokens' => $tokenData,
            'count' => count($tokenData),
        ]);
    }

    #[Route('/tokens/{platform}', name: 'token_delete', methods: ['DELETE'])]
    public function deleteToken(string $platform, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['error' => 'User must be authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $validPlatforms = [
            Token::PLATFORM_SPOTIFY
        ];

        if (!in_array($platform, $validPlatforms)) {
            return new JsonResponse(['error' => 'Invalid platform'], Response::HTTP_BAD_REQUEST);
        }

        $token = $this->tokenRepository->findByUserAndPlatform($user, $platform);
        if (!$token) {
            return new JsonResponse(['error' => 'Token not found for this platform'], Response::HTTP_NOT_FOUND);
        }

        $this->tokenRepository->remove($token, true);

        return new JsonResponse([
            'success' => true,
            'message' => 'Token deleted successfully',
            'platform' => $platform,
        ]);
    }
}