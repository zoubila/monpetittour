<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Application\DTO\ImportedStage;
use App\Application\DTO\ImportedStageResult;

interface TourDeFrance2026StageDataSourceInterface
{
    /**
     * @return list<ImportedStage>
     */
    public function stages(): array;

    /**
     * @return list<ImportedStageResult>
     */
    public function stageResults(int $stageNumber): array;
}
