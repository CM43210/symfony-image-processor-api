<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\TestImageData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ImageUploadControllerTest extends WebTestCase
{
    public function test_uploads_image_successfully(): void
    {
        $client = static::createClient();

        $uploadedFile = $this->createUploadedFile('test-image.jpg', 'image/jpeg');

        $client->request(
            'POST',
            '/api/images',
            [],
            ['image' => $uploadedFile]
        );

        $this->assertResponseStatusCodeSame(202);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('imageId', $responseData);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $responseData['imageId']
        );
    }

    public function test_returns_validation_error_when_no_file(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/images');

        $this->assertResponseStatusCodeSame(400);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function test_returns_validation_error_for_invalid_mime_type(): void
    {
        $client = static::createClient();

        $tmpPath = sys_get_temp_dir() . '/' . uniqid() . '.txt';
        file_put_contents($tmpPath, 'This is not an image');

        $uploadedFile = new UploadedFile(
            $tmpPath,
            'test.txt',
            'text/plain',
            null,
            true
        );

        $client->request(
            'POST',
            '/api/images',
            [],
            ['image' => $uploadedFile]
        );

        $this->assertResponseStatusCodeSame(400);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);

        @unlink($tmpPath);
    }

    private function createUploadedFile(string $filename, string $mimeType): UploadedFile
    {
        $tmpPath = sys_get_temp_dir() . '/' . uniqid() . '_' . $filename;
        $imageData = base64_decode(TestImageData::MINIMAL_VALID_JPEG);
        file_put_contents($tmpPath, $imageData);

        return new UploadedFile(
            $tmpPath,
            $filename,
            $mimeType,
            null,
            true
        );
    }
}
