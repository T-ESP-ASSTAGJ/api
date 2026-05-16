<?php

declare(strict_types=1);

namespace App\Service\Azure;

interface AzureStorageServiceInterface
{
    public function uploadBase64(string $base64, string $folder = 'uploads'): string;
}
