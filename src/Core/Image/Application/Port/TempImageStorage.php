<?php

declare(strict_types=1);

namespace App\Core\Image\Application\Port;

use App\Core\Image\Domain\ImageFormat;
use App\Core\Image\Domain\ImageId;

interface TempImageStorage
{
    public function moveFromPhpTmp(ImageId $id, ImageFormat $format, string $phpTmpPath): string;
}
