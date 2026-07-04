<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Port\RiderImportSourceInterface;
use App\Application\Repository\RiderWriteRepositoryInterface;

final readonly class ImportRidersHandler
{
    public function __construct(
        private RiderImportSourceInterface $source,
        private RiderWriteRepositoryInterface $riders,
    ) {
    }

    public function __invoke(): void
    {
        $this->riders->replaceAllFromImport($this->source->riders());
    }
}
