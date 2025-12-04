<?php

declare(strict_types=1);

namespace App\Infrastructure\Image\Processing;

use App\Core\Image\Application\Port\ImageProcessingTracker;
use App\Core\Image\Application\View\ImageProcessingStatusView;
use App\Core\Image\Domain\ImageId;
use App\Core\Image\Domain\ProcessingStatus;
use Predis\Client;

final readonly class RedisImageProcessingTracker implements ImageProcessingTracker
{
    public function __construct(
        private Client $redis,
        private int $ttlSeconds = 86400,
    ) {
    }

    private function key(ImageId $id): string
    {
        return 'image:status:' . (string) $id;
    }

    public function start(ImageId $id): void
    {
        $data = [
            'status' => ProcessingStatus::QUEUED->value,
            'progress' => 0,
            'message' => null,
            'started_at' => (new \DateTimeImmutable())->format(\DATE_ATOM),
            'finished_at' => null,
        ];

        $this->redis->set($this->key($id), json_encode($data));
        $this->redis->expire($this->key($id), $this->ttlSeconds);
    }

    public function updateProgress(ImageId $id, int $progress, ?string $message = null): void
    {
        $json = $this->redis->get($this->key($id));
        if ($json === null) {
            return;
        }

        $data = json_decode($json, true);
        $data['status'] = ProcessingStatus::PROCESSING->value;
        $data['progress'] = max(0, min(100, $progress));
        if ($message !== null) {
            $data['message'] = $message;
        }

        $this->redis->set($this->key($id), json_encode($data));
    }

    public function complete(ImageId $id): void
    {
        $json = $this->redis->get($this->key($id));
        if ($json === null) {
            return;
        }

        $data = json_decode($json, true);
        $data['status'] = ProcessingStatus::COMPLETED->value;
        $data['progress'] = 100;
        $data['message'] = null;
        $data['finished_at'] = (new \DateTimeImmutable())->format(\DATE_ATOM);

        $this->redis->set($this->key($id), json_encode($data));
    }

    public function fail(ImageId $id, string $error): void
    {
        $json = $this->redis->get($this->key($id));
        $data = $json !== null ? json_decode($json, true) : [];

        $data['status'] = ProcessingStatus::FAILED->value;
        $data['progress'] = $data['progress'] ?? 0;
        $data['message'] = $error;
        $data['finished_at'] = (new \DateTimeImmutable())->format(\DATE_ATOM);

        $this->redis->set($this->key($id), json_encode($data));
    }

    public function get(ImageId $id): ?ImageProcessingStatusView
    {
        $json = $this->redis->get($this->key($id));
        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true);

        return new ImageProcessingStatusView(
            status: ProcessingStatus::from($data['status']),
            progress: (int) ($data['progress'] ?? 0),
            message: $data['message'] ?? null,
            startedAt: isset($data['started_at']) ? new \DateTimeImmutable($data['started_at']) : null,
            finishedAt: isset($data['finished_at']) ? new \DateTimeImmutable($data['finished_at']) : null,
        );
    }
}
