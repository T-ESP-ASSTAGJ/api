<?php

declare(strict_types=1);

namespace App\Service\Azure;

use AzureOss\Storage\Blob\BlobServiceClient;

/**
 * Fabrique statique pour créer des instances de client Azure Blob Storage à partir d'une chaîne de connexion.
 *
 * Utilisé dans la configuration du conteneur de services pour câbler l'adaptateur Flysystem Azure.
 */
class AzureClientFactory
{
    public static function createServiceClient(string $connectionString): BlobServiceClient
    {
        return BlobServiceClient::fromConnectionString($connectionString);
    }

    public static function createContainerClient(BlobServiceClient $serviceClient, string $containerName): \AzureOss\Storage\Blob\BlobContainerClient
    {
        return $serviceClient->getContainerClient($containerName);
    }
}
