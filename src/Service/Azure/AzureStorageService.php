<?php

declare(strict_types=1);

namespace App\Service\Azure;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;

final class AzureStorageService
{
    public function __construct(
        private readonly FilesystemOperator $azureStorage,
        private readonly string $azureBaseUrl,
    ) {
    }

    /**
     * Handles Base64 strings sent from Swift (Data URI or raw Base64).
     */
    public function uploadBase64(string $base64, string $folder = 'uploads'): string
    {
        // 1. Decode the string and extract the MimeType
        [$mimeType, $binaryData] = $this->decodeBase64($base64);

        // 2. Determine extension and path
        $extension = $this->extensionFromMime($mimeType);
        $path = \sprintf(
            '%s/%s/%s.%s',
            trim($folder, '/'),
            date('Y-m-d'),
            bin2hex(random_bytes(16)),
            $extension,
        );

        try {
            // 3. Flysystem writes the binary and sets the Content-Type header
            // We pass it as httpHeaders.contentType for the azure-oss adapter
            $this->azureStorage->write($path, $binaryData, [
                'httpHeaders' => [
                    'contentType' => $mimeType,
                ],
            ]);

            return rtrim($this->azureBaseUrl, '/').'/'.$path;
        } catch (FilesystemException $e) {
            // Log this or handle Azure-specific errors
            throw new \RuntimeException('Azure Upload Failed: '.$e->getMessage().' | Previous: '.($e->getPrevious()?->getMessage() ?? 'none'));
        }
    }

    /**
     * @return array<int, string>
     */
    private function decodeBase64(string $base64): array
    {
        $mimeType = 'image/jpeg'; // Default

        // Swift often sends strings like "data:image/png;base64,iVBO..."
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
