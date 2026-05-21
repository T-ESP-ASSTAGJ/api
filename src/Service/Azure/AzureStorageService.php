<?php

declare(strict_types=1);

namespace App\Service\Azure;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;

/**
 * Téléverse des données de fichier encodées en base64 vers Azure Blob Storage via Flysystem et retourne l'URL CDN publique.
 *
 * Accepte aussi bien les chaînes Base64 brutes que le format Data URI (`data:<mime>;base64,<data>`), qui est le
 * format généralement envoyé par les clients iOS/Swift. Les fichiers sont stockés sous un chemin partitionné par date
 * (`{folder}/{YYYY-MM-DD}/{random-hex}.{ext}`) pour maintenir les conteneurs gérables.
 */
final class AzureStorageService implements AzureStorageServiceInterface
{
    public function __construct(
        private readonly FilesystemOperator $azureStorage,
        private readonly string $azureBaseUrl,
    ) {
    }

    /**
     * Gère les chaînes Base64 envoyées depuis Swift (Data URI ou Base64 brut).
     */
    public function uploadBase64(string $base64, string $folder = 'uploads'): string
    {
        // 1. Décoder la chaîne et extraire le type MIME
        [$mimeType, $binaryData] = $this->decodeBase64($base64);

        // 2. Déterminer l'extension et le chemin de stockage
        $extension = $this->extensionFromMime($mimeType);
        $path = \sprintf(
            '%s/%s/%s.%s',
            trim($folder, '/'),
            date('Y-m-d'),
            bin2hex(random_bytes(16)),
            $extension,
        );

        try {
            // 3. Flysystem écrit le binaire et définit le Content-Type via l'adaptateur azure-oss
            $this->azureStorage->write($path, $binaryData, [
                'httpHeaders' => [
                    'contentType' => $mimeType,
                ],
            ]);

            return rtrim($this->azureBaseUrl, '/').'/'.$path;
        } catch (FilesystemException $e) {
            // Propager l'erreur avec le message Azure pour faciliter le diagnostic
            throw new \RuntimeException('Azure Upload Failed: '.$e->getMessage().' | Previous: '.($e->getPrevious()?->getMessage() ?? 'none'));
        }
    }

    /**
     * @return array<int, string>
     */
    private function decodeBase64(string $base64): array
    {
        $mimeType = 'image/jpeg'; // Default

        // Les clients Swift envoient souvent le format "data:image/png;base64,iVBO..."
        if (str_contains($base64, ',')) {
            [$meta, $base64] = explode(',', $base64, 2);
            if (preg_match('/data:([^;]+);/', $meta, $matches)) {
                $mimeType = $matches[1];
            }
        }

        $binary = base64_decode($base64, true);

        if (false === $binary) {
            throw new \InvalidArgumentException('The provided string is not valid Base64 data.');
        }

        return [$mimeType, $binary];
    }

    private function extensionFromMime(string $mimeType): string
    {
        return match ($mimeType) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/heic' => 'heic', // Common for iOS/Swift
            default => 'jpg',
        };
    }
}
