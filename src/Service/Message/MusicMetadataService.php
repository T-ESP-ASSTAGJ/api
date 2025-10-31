<?php

declare(strict_types=1);

namespace App\Service\Message;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\TokenRepository;
use App\Service\Spotify\SpotifyService;

readonly class MusicMetadataService
{
    public function __construct(
        private SpotifyService $spotifyService,
        private TokenRepository $tokenRepository,
    ) {
    }

    /**
     * @param array{platform: string, track_id: string, fallback_ids: array<string, string>} $track
     *
     * @return array{title: string, artist: string, album_cover: string, preview_url: string|null, platform_link: string, availability: string}
     */
    public function getTrackMetadata(array $track, ?User $recipient = null): array
    {
        $targetPlatform = $this->getTargetPlatform($track, $recipient);
        $trackId = $this->getTrackIdForPlatform($track, $targetPlatform);

        if (!$trackId) {
            return $this->createUnavailableMetadata($track);
        }

        try {
            $accessToken = $this->getAccessTokenForPlatform($targetPlatform);

            if (!$accessToken) {
                return $this->createUnavailableMetadata($track);
            }

            return $this->fetchMetadataFromPlatform($targetPlatform, $trackId, $accessToken);
        } catch (\Throwable $e) {
            return $this->createUnavailableMetadata($track);
        }
    }

    /**
     * @param array{platform: string, track_id: string, fallback_ids: array<string, string>} $track
     */
    private function getTargetPlatform(array $track, ?User $recipient): string
    {
        if (!$recipient) {
            return $track['platform'];
        }

        $recipientToken = $this->tokenRepository->findOneBy([
            'user' => $recipient,
            'platform' => Token::PLATFORM_SPOTIFY,
        ]);

        if ($recipientToken && !$recipientToken->isExpired()) {
            return Token::PLATFORM_SPOTIFY;
        }

        return $track['platform'];
    }

    /**
     * @param array{platform: string, track_id: string, fallback_ids: array<string, string>} $track
     */
    private function getTrackIdForPlatform(array $track, string $platform): ?string
    {
        if ($track['platform'] === $platform) {
            return $track['track_id'];
        }

        return $track['fallback_ids'][$platform] ?? null;
    }

    private function getAccessTokenForPlatform(string $platform): ?string
    {
        $token = $this->tokenRepository->findOneBy(['platform' => $platform]);

        if (!$token || $token->isExpired()) {
            return null;
        }

        return $token->getAccessToken();
    }

    /**
     * @return array{title: string, artist: string, album_cover: string, preview_url: string|null, platform_link: string, availability: string}
     */
    private function fetchMetadataFromPlatform(string $platform, string $trackId, string $accessToken): array
    {
        switch ($platform) {
            case Token::PLATFORM_SPOTIFY:
                $trackDTO = $this->spotifyService->getTrack($accessToken, $trackId);

                return [
                    'title' => $trackDTO->name,
                    'artist' => implode(', ', $trackDTO->artists),
                    'album_cover' => $trackDTO->imageUrl ?? '',
                    'preview_url' => $trackDTO->previewUrl,
                    'platform_link' => $trackDTO->externalUrl,
                    'availability' => 'available',
                ];

            default:
                throw new \InvalidArgumentException("Platform {$platform} not supported");
        }
    }

    /**
     * @param array{platform: string, track_id: string, fallback_ids: array<string, string>} $track
     *
     * @return array{title: string, artist: string, album_cover: string, preview_url: string|null, platform_link: string, availability: string}
     */
    private function createUnavailableMetadata(array $track): array
    {
        return [
            'title' => 'Morceau indisponible',
            'artist' => '',
            'album_cover' => '',
            'preview_url' => null,
            'platform_link' => '',
            'availability' => 'unavailable',
        ];
    }
}
