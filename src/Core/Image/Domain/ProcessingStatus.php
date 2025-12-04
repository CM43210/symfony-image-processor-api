<?php

declare(strict_types=1);

namespace App\Core\Image\Domain;

enum ProcessingStatus: string
{
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
