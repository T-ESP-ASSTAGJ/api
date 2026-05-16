<?php

declare(strict_types=1);

namespace App\Tests\Service\Azure;

use App\Service\Azure\AzureStorageService;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;

class AzureStorageServiceTest extends TestCase
{
    private FilesystemOperator&\PHPUnit\Framework\MockObject\MockObject $storage;

    private AzureStorageService $service;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(FilesystemOperator::class);
        $this->service = new AzureStorageService($this->storage, 'https://cdn.example.com');
    }

    public function testUploadBase64WithDataUriPrefixReturnsUrl(): void
    {
        $base64 = 'data:image/png;base64,'.base64_encode('fake-png-content');

        $this->storage->expects($this->once())->method('write');

        $url = $this->service->uploadBase64($base64, 'uploads');

        $this->assertStringStartsWith('https://cdn.example.com/uploads/', $url);
        $this->assertStringEndsWith('.png', $url);
    }

    public function testUploadBase64WithRawBase64UsesDefaultMimeType(): void
    {
        $base64 = base64_encode('fake-jpeg-content');

        $this->storage->expects($this->once())->method('write');

        $url = $this->service->uploadBase64($base64);

        $this->assertStringEndsWith('.jpg', $url);
    }

    public function testUploadBase64AllMimeTypeExtensions(): void
    {
        foreach ([
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/heic' => 'heic',
            'image/jpeg' => 'jpg',
        ] as $mimeType => $expectedExt) {
            $base64 = 'data:'.$mimeType.';base64,'.base64_encode('content');
            $this->storage->method('write');

            $url = $this->service->uploadBase64($base64);

            $this->assertStringEndsWith('.'.$expectedExt, $url, "Expected extension {$expectedExt} for MIME {$mimeType}");
        }
    }

    public function testUploadBase64ThrowsRuntimeExceptionOnFlysystemError(): void
    {
        $base64 = base64_encode('content');

        $this->storage->method('write')
            ->willThrowException($this->createMock(FilesystemException::class))
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Azure Upload Failed');

        $this->service->uploadBase64($base64);
    }

    public function testUploadBase64ThrowsOnInvalidBase64(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->uploadBase64('data:image/jpeg;base64,!!!not-valid-base64!!!');
    }
}
