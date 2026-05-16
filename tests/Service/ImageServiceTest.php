<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Azure\AzureStorageServiceInterface;
use App\Service\ImageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ImageServiceTest extends TestCase
{
    // Tiny 1x1 transparent GIF — valid image data for getimagesizefromstring
    private const VALID_GIF_B64 = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    private Filesystem&\PHPUnit\Framework\MockObject\MockObject $filesystem;

    private AzureStorageServiceInterface&\PHPUnit\Framework\MockObject\MockObject $azureStorage;

    private ImageService $service;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->azureStorage = $this->createMock(AzureStorageServiceInterface::class);
        $this->service = new ImageService('/app', $this->filesystem, $this->azureStorage);
    }

    // --- saveBase64ToStorage ---

    public function testSaveBase64ToStorageReturnsNullForEmptyString(): void
    {
        $this->azureStorage->expects($this->never())->method('uploadBase64');

        $this->assertNull($this->service->saveBase64ToStorage('', 'uploads'));
    }

    public function testSaveBase64ToStorageDelegatesToAzure(): void
    {
        $this->azureStorage->method('uploadBase64')->with('data', 'covers')->willReturn('https://cdn.example.com/image.jpg');

        $result = $this->service->saveBase64ToStorage('data', 'covers');

        $this->assertSame('https://cdn.example.com/image.jpg', $result);
    }

    // --- saveBase64Image ---

    public function testSaveBase64ImageReturnsNullForNull(): void
    {
        $this->assertNull($this->service->saveBase64Image(null));
    }

    public function testSaveBase64ImageReturnsNullForEmptyString(): void
    {
        $this->assertNull($this->service->saveBase64Image(''));
    }

    public function testSaveBase64ImageReturnsHttpUrlAsIs(): void
    {
        $url = 'http://example.com/image.png';
        $this->assertSame($url, $this->service->saveBase64Image($url));
    }

    public function testSaveBase64ImageReturnsHttpsUrlAsIs(): void
    {
        $url = 'https://cdn.example.com/image.jpg';
        $this->assertSame($url, $this->service->saveBase64Image($url));
    }

    public function testSaveBase64ImageThrowsWhenNoBase64Marker(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid base64 image format');

        $this->service->saveBase64Image('data:image/jpeg;encoding,abc');
    }

    public function testSaveBase64ImageThrowsWhenMimeTypeInvalid(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid image MIME type');

        $this->service->saveBase64Image('no-mime-header;base64,'.base64_encode('data'));
    }

    public function testSaveBase64ImageThrowsForUnsupportedExtension(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Unsupported image format');

        $this->service->saveBase64Image('data:image/bmp;base64,'.base64_encode('data'));
    }

    public function testSaveBase64ImageThrowsForInvalidBase64Data(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Failed to decode base64 image');

        $this->service->saveBase64Image('data:image/png;base64,!!!invalid!!!');
    }

    public function testSaveBase64ImageThrowsWhenExceedsMaxSize(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Image exceeds maximum size of 5MB');

        $largeData = str_repeat('A', 5 * 1024 * 1024 + 1);
        $this->service->saveBase64Image('data:image/png;base64,'.base64_encode($largeData));
    }

    public function testSaveBase64ImageThrowsForInvalidImageContent(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid image data or corrupted file');

        $this->service->saveBase64Image('data:image/png;base64,'.base64_encode('not-an-image'));
    }

    public function testSaveBase64ImageSavesValidGifAndReturnsPath(): void
    {
        $this->filesystem->method('exists')->willReturn(true);
        $this->filesystem->expects($this->never())->method('mkdir');
        $this->filesystem->expects($this->once())->method('dumpFile');

        $result = $this->service->saveBase64Image('data:image/gif;base64,'.self::VALID_GIF_B64);

        $this->assertStringStartsWith('/uploads/posts/', $result);
        $this->assertStringEndsWith('.gif', $result);
    }

    public function testSaveBase64ImageCreatesDirectoryWhenMissing(): void
    {
        $this->filesystem->method('exists')->willReturn(false);
        $this->filesystem->expects($this->once())->method('mkdir');
        $this->filesystem->expects($this->once())->method('dumpFile');

        $this->service->saveBase64Image('data:image/gif;base64,'.self::VALID_GIF_B64);
    }

    public function testSaveBase64ImageNormalizesJpegExtension(): void
    {
        $this->filesystem->method('exists')->willReturn(true);
        $this->filesystem->method('dumpFile');

        $result = $this->service->saveBase64Image('data:image/jpeg;base64,'.self::VALID_GIF_B64);

        $this->assertStringEndsWith('.jpg', $result);
    }

    // --- deleteImage ---

    public function testDeleteImageDoesNothingForNull(): void
    {
        $this->filesystem->expects($this->never())->method('exists');
        $this->service->deleteImage(null);
    }

    public function testDeleteImageDoesNothingForHttpUrl(): void
    {
        $this->filesystem->expects($this->never())->method('exists');
        $this->service->deleteImage('http://cdn.example.com/image.jpg');
    }

    public function testDeleteImageDoesNothingForHttpsUrl(): void
    {
        $this->filesystem->expects($this->never())->method('exists');
        $this->service->deleteImage('https://cdn.example.com/image.jpg');
    }

    public function testDeleteImageRemovesLocalFileWhenExists(): void
    {
        $this->filesystem->method('exists')->with('/app/public/uploads/posts/img.jpg')->willReturn(true);
        $this->filesystem->expects($this->once())->method('remove')->with('/app/public/uploads/posts/img.jpg');

        $this->service->deleteImage('/uploads/posts/img.jpg');
    }

    public function testDeleteImageDoesNothingWhenLocalFileDoesNotExist(): void
    {
        $this->filesystem->method('exists')->willReturn(false);
        $this->filesystem->expects($this->never())->method('remove');

        $this->service->deleteImage('/uploads/posts/missing.jpg');
    }
}
