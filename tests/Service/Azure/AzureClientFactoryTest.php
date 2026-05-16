<?php

declare(strict_types=1);

namespace App\Tests\Service\Azure;

use App\Service\Azure\AzureClientFactory;
use AzureOss\Storage\Blob\BlobContainerClient;
use AzureOss\Storage\Blob\BlobServiceClient;
use PHPUnit\Framework\TestCase;

class AzureClientFactoryTest extends TestCase
{
    private const CONNECTION_STRING = 'DefaultEndpointsProtocol=https;AccountName=devstoreaccount1;AccountKey=Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==;EndpointSuffix=core.windows.net';

    public function testCreateServiceClientReturnsBlobServiceClient(): void
    {
        $client = AzureClientFactory::createServiceClient(self::CONNECTION_STRING);

        $this->assertInstanceOf(BlobServiceClient::class, $client);
    }

    public function testCreateContainerClientReturnsBlobContainerClient(): void
    {
        $serviceClient = AzureClientFactory::createServiceClient(self::CONNECTION_STRING);

        $containerClient = AzureClientFactory::createContainerClient($serviceClient, 'my-container');

        $this->assertInstanceOf(BlobContainerClient::class, $containerClient);
    }
}
