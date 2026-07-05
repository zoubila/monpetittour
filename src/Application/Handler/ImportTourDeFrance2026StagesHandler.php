<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\ImportedStage;
use App\Application\Port\TourDeFrance2026StageDataSourceInterface;
use App\Infrastructure\Doctrine\Entity\StageRecord;
use App\Infrastructure\Doctrine\Repository\StageRecordRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ImportTourDeFrance2026StagesHandler
{
    public function __construct(
        private TourDeFrance2026StageDataSourceInterface $source,
        private StageRecordRepository $stages,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(): int
    {
        $importedCount = 0;

        foreach ($this->source->stages() as $stage) {
            $this->upsert($stage);
            ++$importedCount;
        }

        $this->entityManager->flush();

        return $importedCount;
    }

    private function upsert(ImportedStage $importedStage): void
    {
        $stage = $this->stages->findOneByNumber($importedStage->number);

        if (!$stage instanceof StageRecord) {
            $this->stages->save(new StageRecord(
                $importedStage->number,
                $importedStage->startLocation,
                $importedStage->finishLocation,
                $importedStage->distanceInKilometers,
                $importedStage->positiveElevationInMeters,
                $importedStage->mapPath,
            ));

            return;
        }

        $stage->updateFromImport(
            $importedStage->startLocation,
            $importedStage->finishLocation,
            $importedStage->distanceInKilometers,
            $importedStage->positiveElevationInMeters,
            $importedStage->mapPath,
        );
    }
}
