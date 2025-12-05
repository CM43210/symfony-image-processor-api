<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Core\Image\Application\Port\ImageRepository;
use App\Core\Image\Domain\Image;
use App\Core\Image\Domain\ImageFile;
use App\Core\Image\Domain\ImageFormat;
use App\Core\Image\Domain\ImageId;
use App\Tests\TestImageData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ImageDownloadControllerTest extends WebTestCase
{
    public function test_downloads_processed_image_archive(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $imageId = ImageId::generate();
        $archivePath = $this->createTestArchive($imageId);

        $imageFile = ImageFile::create('test.jpg', ImageFormat::JPEG, 1024);
        $image = Image::upload($imageId, $imageFile);
        $image->setProcessedArchive(basename($archivePath));

        $repository = $container->get(ImageRepository::class);
        $repository->save($image);

        $client->request('GET', '/api/images/' . (string) $imageId . '/download');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/zip');
        $this->assertResponseHasHeader('content-disposition');

        $contentDisposition = $client->getResponse()->headers->get('content-disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('.zip', $contentDisposition);

        @unlink($archivePath);
    }

    public function test_returns_404_when_image_not_found(): void
    {
        $client = static::createClient();

        $nonExistentId = ImageId::generate();

        $client->request('GET', '/api/images/' . (string) $nonExistentId . '/download');

        $this->assertResponseStatusCodeSame(404);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('title', $responseData);
    }

    public function test_returns_404_when_archive_not_processed_yet(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $imageId = ImageId::generate();
        $imageFile = ImageFile::create('test.jpg', ImageFormat::JPEG, 1024);
        $image = Image::upload($imageId, $imageFile);

        $repository = $container->get(ImageRepository::class);
        $repository->save($image);

        $client->request('GET', '/api/images/' . (string) $imageId . '/download');

        $this->assertResponseStatusCodeSame(404);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('title', $responseData);
    }

    public function test_returns_400_for_invalid_uuid(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/images/invalid-uuid/download');

        $this->assertResponseStatusCodeSame(400);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('title', $responseData);
    }

    private function createTestArchive(ImageId $imageId): string
    {
        $container = static::getContainer();
        $archiveDir = $container->getParameter('archive.storage_dir');

        if (!is_dir($archiveDir)) {
            mkdir($archiveDir, 0755, true);
        }

        $archiveName = (string) $imageId . '.zip';
        $archivePath = $archiveDir . '/' . $archiveName;

        $zip = new \ZipArchive();
        $zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $testImageData = base64_decode(TestImageData::MINIMAL_VALID_JPEG);
        $zip->addFromString('original.jpg', $testImageData);
        $zip->addFromString('thumbnail.webp', $testImageData);
        $zip->addFromString('medium.webp', $testImageData);
        $zip->addFromString('large.webp', $testImageData);
        $zip->close();

        return $archivePath;
    }
}
