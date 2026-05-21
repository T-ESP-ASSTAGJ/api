<?php

declare(strict_types=1);

namespace App\Service\Spotify;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Gère le flux de code d'autorisation OAuth2 de Spotify.
 *
 * Responsabilités : construction de l'URL d'autorisation, échange du code de callback contre des jetons d'accès/rafraîchissement,
 * persistance des jetons en base de données, et rafraîchissement des jetons expirés.
 * Les jetons existants pour le même utilisateur et la même plateforme sont remplacés à chaque échange réussi.
 */
readonly class SpotifyAuthService
{
    public const SPOTIFY_TOKEN_URL = 'https://accounts.spotify.com/api/token';
    public const SPOTIFY_AUTH_URL = 'https://accounts.spotify.com/authorize';

    public function __construct(
        private HttpClientInterface $spotifyApiClient,
        private HttpClientInterface $spotifyAuthClient,
        private TokenRepository $tokenRepository,
        private EntityManagerInterface $entityManager,
        #[Autowire(env: 'SPOTIFY_CLIENT_ID')]
        private string $clientId,
        #[Autowire(env: 'SPOTIFY_CLIENT_SECRET')]
        private string $clientSecret,
        #[Autowire(env: 'SPOTIFY_REDIRECT_URI')]
        private string $redirectUri,
    ) {
    }

    /** Construit l'URL d'autorisation OAuth2 Spotify avec les scopes requis et un jeton d'état CSRF. */
    public function getRedirectUri(string $state): string
    {
        return self::SPOTIFY_AUTH_URL
            .'?response_type=code'
            .'&client_id='.$this->clientId
            .'&scope=user-read-email user-read-private playlist-read-private'
            .'&redirect_uri='.$this->redirectUri
            .'&state='.urlencode($state);
    }

    /**
     * Échange un code d'autorisation OAuth2 contre des jetons d'accès/rafraîchissement et les persiste.
     *
     * Tout jeton Spotify préexistant pour cet utilisateur est supprimé avant la sauvegarde du nouveau.
     *
     * @throws \RuntimeException en cas d'échec de l'API Spotify ou d'absence d'access_token dans la réponse
     */
    public function exchangeCodeForToken(string $code, User $user): Token
    {
        try {
            $response = $this->spotifyAuthClient->request('POST', self::SPOTIFY_TOKEN_URL, [
                'body' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri,
                ],
            ]);

            $tokenData = $response->toArray();

            if (!isset($tokenData['access_token'])) {
                throw new \RuntimeException('No access token received from Spotify');
            }

            // Supprimer le token existant pour cet utilisateur et cette plateforme
            $existingToken = $this->tokenRepository->findByUserAndPlatform($user, Token::PLATFORM_SPOTIFY);
            if ($existingToken) {
                $this->entityManager->remove($existingToken);
            }

            // Créer le nouveau token
            $token = new Token();
            $token->setUser($user)
                ->setPlatform(Token::PLATFORM_SPOTIFY)
                ->setAccessToken($tokenData['access_token'])
                ->setRefreshToken($tokenData['refresh_token'] ?? null)
                ->setExpiresAt(new \DateTime('+'.($tokenData['expires_in'] ?? 3600).' seconds'))
                ->setScopes(explode(' ', $tokenData['scope'] ?? ''))
            ;

            // Récupérer le profil Spotify pour associer l'identifiant de la plateforme
            $userProfile = $this->getUserProfile($tokenData['access_token']);
            $token->setPlatformUserId($userProfile['id']);

            $this->entityManager->persist($token);
            $this->entityManager->flush();

            return $token;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to exchange code for token: '.$e->getMessage());
        }
    }

    /**
     * Rafraîchit un jeton d'accès Spotify expiré en utilisant le jeton de rafraîchissement stocké.
     *
     * @throws \InvalidArgumentException si le jeton n'est pas pour la plateforme Spotify
     * @throws \RuntimeException si aucun jeton de rafraîchissement n'est disponible ou si l'appel API échoue
     */
    public function refreshToken(Token $token): Token
    {
        if (Token::PLATFORM_SPOTIFY !== $token->getPlatform()) {
            throw new \InvalidArgumentException('Token is not for Spotify platform');
        }

        if (!$token->getRefreshToken()) {
            throw new \RuntimeException('No refresh token available');
        }

        try {
            $response = $this->spotifyAuthClient->request('POST', self::SPOTIFY_TOKEN_URL, [
                'body' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $token->getRefreshToken(),
                ],
            ]);

            $tokenData = $response->toArray();

            $token->setAccessToken($tokenData['access_token'])
                ->setExpiresAt(new \DateTime('+'.($tokenData['expires_in'] ?? 3600).' seconds'))
            ;

            if (isset($tokenData['refresh_token'])) {
                $token->setRefreshToken($tokenData['refresh_token']);
            }

            if (isset($tokenData['scope'])) {
                $token->setScopes(explode(' ', $tokenData['scope']));
            }

            $this->entityManager->persist($token);
            $this->entityManager->flush();

            return $token;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to refresh token: '.$e->getMessage());
        }
    }

    /** Retourne true si le jeton d'accès est accepté par le point de terminaison Spotify /me. */
    public function validateToken(string $accessToken): bool
    {
        try {
            $this->getUserProfile($accessToken);

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * @return array<string>
     */
    private function getUserProfile(string $accessToken): array
    {
        try {
            $response = $this->spotifyApiClient->request('GET', 'me', [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                ],
            ]);

            return $response->toArray();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get user profile: '.$e->getMessage());
        }
    }
}
