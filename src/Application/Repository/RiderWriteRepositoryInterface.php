<?php

declare(strict_types=1);

namespace App\Application\Repository;

use App\Application\DTO\ImportedRider;

interface RiderWriteRepositoryInterface
{
    /**
     * @param iterable<ImportedRider> $riders
     */
    public function replaceAllFromImport(iterable $riders): void;
}
