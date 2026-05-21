<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Azure\AzureStorageServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Uuid;

/**
 * Gère les entrées d'images encodées en base64 : valide le format et la taille, puis stocke le fichier.
 *
 * Le stockage principal est Azure Blob Storage via {@see AzureStorageServiceInterface}.
 * Un repli sur le système de fichiers local existe pour le développement ou lorsqu'Azure est indisponible.
 * Toutes les méthodes publiques retournent une URL publique ou null si aucune image n'a été fournie.
 */
readonly class ImageService
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private Filesystem $filesystem,
        private AzureStorageServiceInterface $azureStorage,
    ) {
    }

    /**
     * Téléverse une image base64 directement vers Azure Blob Storage et retourne son URL publique.
     */
    public function saveBase64ToStorage(string $base64Image, string $folder): ?string
    {
        if (!$base64Image) {
            return null;
        }

        return $this->azureStorage->uploadBase64($base64Image, $folder);
    }

    /**
     * Valide et enregistre une image base64 sur le système de fichiers local, en retournant son chemin d'URL publique.
     *
     * Passthrough : si la valeur est déjà une URL HTTP(S), elle est retournée telle quelle.
     * Valide le type MIME (jpg, png, gif, webp) et la taille maximale (5 Mo) avant l'écriture.
     *
     * @throws BadRequestHttpException en cas de format invalide, de type non supporté ou d'entrée trop volumineuse
     */
    public function saveBase64Image(?string $base64Image, string $directory = 'posts'): ?string
    {
        if (!$base64Image) {
            return null;
        }

        // Si c'est déjà une URL, la retourner telle quelle
        if (str_starts_with($base64Image, 'http://') || str_starts_with($base64Image, 'https://')) {
            return $base64Image;
        }

        // Extraire les données base64
        if (!str_contains($base64Image, 'base64,')) {
            throw new BadRequestHttpException('Invalid base64 image format');
        }

        [$metadata, $base64Data] = explode('base64,', $base64Image, 2);

        // Extraire le type MIME
        preg_match('/data:image\/([a-zA-Z]+);/', $metadata, $matches);
        if (!isset($matches[1])) {
            throw new BadRequestHttpException('Invalid image MIME type');
        }

        $extension = strtolower($matches[1]);
        if ('jpeg' === $extension) {
            $extension = 'jpg';
        }

        $allowedExtensions = ['jpg', 'png', 'gif', 'webp'];
        if (!\in_array($extension, $allowedExtensions, true)) {
            throw new BadRequestHttpException('Unsupported image format. Allowed: '.implode(', ', $allowedExtensions));
        }

        // Décoder le base64
        $imageData = base64_decode($base64Data, true);
        if (false === $imageData) {
            throw new BadRequestHttpException('Failed to decode base64 image');
        }

        // Valider la taille réelle du fichier (après décodage)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if (\strlen($imageData) > $maxSize) {
            throw new BadRequestHttpException('Image exceeds maximum size of 5MB');
        }

        // Vérifier que les données correspondent bien à une image valide
        $imageInfo = @getimagesizefromstring($imageData);
        if (false === $imageInfo) {
            throw new BadRequestHttpException('Invalid image data or corrupted file');
        }

        // Générer un nom de fichier unique via l'UUID Symfony
        $filename = Uuid::v4()->toRfc4122().'.'.$extension;
        $uploadPath = \sprintf('%s/public/uploads/%s', $this->projectDir, $directory);

        // Créer le répertoire s'il n'existe pas
        if (!$this->filesystem->exists($uploadPath)) {
            $this->filesystem->mkdir($uploadPath, 0o755);
        }

        // Écrire le fichier sur le disque
        $filePath = $uploadPath.'/'.$filename;
        $this->filesystem->dumpFile($filePath, $imageData);

        // Retourner l'URL publique
        return \sprintf('/uploads/%s/%s', $directory, $filename);
    }

    /**
     * Supprime une image stockée localement par son chemin d'URL publique. Sans effet pour les URLs distantes ou null.
     */
    public function deleteImage(?string $imageUrl): void
    {
        if (!$imageUrl || str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return;
        }

        $filePath = $this->projectDir.'/public'.$imageUrl;
        if ($this->filesystem->exists($filePath)) {
            $this->filesystem->remove($filePath);
        }
    }
}
