<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Uuid;

final readonly class ImageService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private Filesystem $filesystem,
    ) {
    }

    public function saveBase64Image(?string $base64Image, string $directory = 'posts'): ?string
    {
        if (!$base64Image) {
            return null;
        }

        // Check if it's already a URL (for backward compatibility)
        if (str_starts_with($base64Image, 'http://') || str_starts_with($base64Image, 'https://')) {
            return $base64Image;
        }

        // Extract the base64 data
        if (!str_contains($base64Image, 'base64,')) {
            throw new BadRequestHttpException('Invalid base64 image format');
        }

        [$metadata, $base64Data] = explode('base64,', $base64Image, 2);

        // Extract MIME type
        preg_match('/data:image\/([a-zA-Z]+);/', $metadata, $matches);
        if (!isset($matches[1])) {
            throw new BadRequestHttpException('Invalid image MIME type');
        }

        $extension = strtolower($matches[1]);
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $allowedExtensions = ['jpg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new BadRequestHttpException('Unsupported image format. Allowed: ' . implode(', ', $allowedExtensions));
        }

        // Decode base64
        $imageData = base64_decode($base64Data, true);
        if ($imageData === false) {
            throw new BadRequestHttpException('Failed to decode base64 image');
        }

        // Validate actual file size (after decoding)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if (strlen($imageData) > $maxSize) {
            throw new BadRequestHttpException('Image exceeds maximum size of 5MB');
        }

        // Validate it's actually a valid image
        $imageInfo = @getimagesizefromstring($imageData);
        if ($imageInfo === false) {
            throw new BadRequestHttpException('Invalid image data or corrupted file');
        }

        // Generate unique filename using Symfony's UUID
        $filename = Uuid::v4()->toRfc4122() . '.' . $extension;
        $uploadPath = sprintf('%s/public/uploads/%s', $this->projectDir, $directory);

        // Ensure directory exists using Symfony Filesystem
        if (!$this->filesystem->exists($uploadPath)) {
            $this->filesystem->mkdir($uploadPath, 0755);
        }

        // Save file using Symfony Filesystem
        $filePath = $uploadPath . '/' . $filename;
        $this->filesystem->dumpFile($filePath, $imageData);

        // Return public URL
        return sprintf('/uploads/%s/%s', $directory, $filename);
    }

    public function deleteImage(?string $imageUrl): void
    {
        if (!$imageUrl || str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return;
        }

        $filePath = $this->projectDir . '/public' . $imageUrl;
        if ($this->filesystem->exists($filePath)) {
            $this->filesystem->remove($filePath);
        }
    }
}
