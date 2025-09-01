<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\SpotifyToken;
use App\Entity\User;
use App\Repository\SpotifyTokenRepository;
use App\Service\SpotifyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth/spotify', name: 'spotify_auth_')]
class SpotifyAuthController extends AbstractController
{
    public function __construct(
        private readonly SpotifyService $spotifyService,
        private readonly EntityManagerInterface $em,
        private readonly SpotifyTokenRepository $spotifyTokenRepository,
    ) {}

    /**
     * Génère l'URL d'autorisation Spotify
     */
    #[Route('/authorize', name: 'authorize', methods: ['GET'])]
    public function authorize(): JsonResponse
    {
        $authUrl = $this->spotifyService->getAuthorizationUrl([
            'user-read-private',
            'user-read-email',
            'playlist-read-private',
            'playlist-read-collaborative',
            'user-library-read',
        ]);

        return new JsonResponse([
            'authorization_url' => $authUrl,
            'message' => 'Visit this URL to authorize the application with Spotify',
        ]);
    }

    /**
     * Callback pour recevoir le code d'autorisation de Spotify - VERSION TEST
     */
    #[Route('/callback', name: 'callback', methods: ['GET'])]
    public function callback(Request $request): JsonResponse
    {
        // TEMPORAIRE : Désactivé pour test OAuth
        // if (!$user) {
        //     return new JsonResponse(['error' => 'User must be authenticated'], Response::HTTP_UNAUTHORIZED);
        // }

        $code = $request->query->get('code');
        $error = $request->query->get('error');
        $state = $request->query->get('state');

        // Vérification des erreurs d'autorisation
        if ($error) {
            return new JsonResponse([
                'error' => 'Authorization denied: ' . $error,
                'details' => 'User denied access or an error occurred during authorization'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérification de la présence du code
        if (!$code) {
            return new JsonResponse([
                'error' => 'Authorization code not provided',
                'details' => 'No authorization code received from Spotify'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validation basique du state
        if (empty($state)) {
            return new JsonResponse([
                'error' => 'Invalid state parameter',
                'details' => 'State parameter is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Échange du code contre un token
            $tokenData = $this->spotifyService->exchangeCodeForToken($code);

            // Récupération du profil utilisateur
            $spotifyProfile = $this->spotifyService->getUserProfile($tokenData['access_token']);

            // POUR TEST : Retournez juste les données sans les sauvegarder
            return new JsonResponse([
                'success' => true,
                'message' => 'TEST: Spotify OAuth completed successfully',
                'test_data' => [
                    'access_token' => substr($tokenData['access_token'], 0, 20) . '...',
                    'expires_in' => $tokenData['expires_in'],
                    'scope' => $tokenData['scope'],
                    'spotify_user' => [
                        'id' => $spotifyProfile['id'],
                        'display_name' => $spotifyProfile['display_name'],
                        'email' => $spotifyProfile['email'],
                        'country' => $spotifyProfile['country'],
                        'followers' => $spotifyProfile['followers']['total'],
                    ],
                ],
                'note' => 'This is a test - no data was saved to database'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed to test Spotify OAuth',
                'details' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
