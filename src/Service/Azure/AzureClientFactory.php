<?php

declare(strict_types=1);

namespace App\Service\Azure;

use AzureOss\Storage\Blob\BlobServiceClient;

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
