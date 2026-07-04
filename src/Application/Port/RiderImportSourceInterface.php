<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Application\DTO\ImportedRider;

interface RiderImportSourceInterface
{
    /**
     * @return list<ImportedRider>
     */
    public function riders(): array;
}
