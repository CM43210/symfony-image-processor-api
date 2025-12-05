<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Core\Image\Application\Port\ImageProcessingTracker;
use App\Core\Image\Domain\ImageId;
use App\Core\Image\Domain\ProcessingStatus;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ImageStatusControllerTest extends WebTestCase
{
    public function test_returns_status_for_existing_image(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $imageId = ImageId::generate();
        $tracker = $container->get(ImageProcessingTracker::class);
        $tracker->start($imageId);
        $tracker->updateProgress($imageId, 50, 'Processing...');

        $client->request('GET', '/api/images/' . (string) $imageId . '/status');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('progress', $responseData);
        $this->assertSame(ProcessingStatus::PROCESSING->value, $responseData['status']);
        $this->assertSame(50, $responseData['progress']);
        $this->assertSame('Processing...', $responseData['message']);
    }

    public function test_returns_404_for_non_existent_image(): void
    {
        $client = static::createClient();

        $nonExistentId = ImageId::generate();

        $client->request('GET', '/api/images/' . (string) $nonExistentId . '/status');

        $this->assertResponseStatusCodeSame(404);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('title', $responseData);
    }

    public function test_returns_400_for_invalid_uuid(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/images/invalid-uuid/status');

        $this->assertResponseStatusCodeSame(400);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('title', $responseData);
    }

    public function test_returns_completed_status(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $imageId = ImageId::generate();
        $tracker = $container->get(ImageProcessingTracker::class);
        $tracker->start($imageId);
        $tracker->complete($imageId);

        $client->request('GET', '/api/images/' . (string) $imageId . '/status');

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(ProcessingStatus::COMPLETED->value, $responseData['status']);
        $this->assertSame(100, $responseData['progress']);
        $this->assertNull($responseData['message']);
    }

    public function test_returns_failed_status_with_error_message(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $imageId = ImageId::generate();
        $tracker = $container->get(ImageProcessingTracker::class);
        $tracker->start($imageId);
        $tracker->fail($imageId, 'Processing failed: out of memory');

        $client->request('GET', '/api/images/' . (string) $imageId . '/status');

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(ProcessingStatus::FAILED->value, $responseData['status']);
        $this->assertSame('Processing failed: out of memory', $responseData['message']);
    }
}
