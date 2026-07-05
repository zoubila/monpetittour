<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class ImportStageResultsReport
{
    /**
     * @param list<string> $unmatchedRiderNames
     */
    public function __construct(
        public int $stageNumber,
        public int $importedResultCount,
        public array $unmatchedRiderNames,
    ) {
    }
}
