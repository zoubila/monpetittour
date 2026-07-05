<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\ImportStageResultsReport;
use App\Application\DTO\ImportedStageResult;
use App\Application\Port\TourDeFrance2026StageDataSourceInterface;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Entity\StageRecord;
use App\Infrastructure\Doctrine\Entity\StageRiderResultRecord;
use App\Infrastructure\Doctrine\Repository\RiderRecordRepository;
use App\Infrastructure\Doctrine\Repository\StageRecordRepository;
use App\Infrastructure\Doctrine\Repository\StageRiderResultRecordRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ImportTourDeFrance2026StageResultsHandler
{
    public function __construct(
        private TourDeFrance2026StageDataSourceInterface $source,
        private ImportTourDeFrance2026StagesHandler $importStages,
        private StageRecordRepository $stages,
        private RiderRecordRepository $riders,
        private StageRiderResultRecordRepository $results,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(int $stageNumber): ImportStageResultsReport
    {
        ($this->importStages)();
        $stage = $this->stages->findOneByNumber($stageNumber);

        if (!$stage instanceof StageRecord) {
            throw new \RuntimeException(sprintf('Stage %d was not found in imported Tour de France stages.', $stageNumber));
        }

        $importedResults = $this->source->stageResults($stageNumber);
        $matchedResults = [];
        $unmatchedRiderNames = [];

        foreach ($importedResults as $importedResult) {
            $rider = $this->riders->findOneByImportedName($importedResult->riderName);

            if (!$rider instanceof RiderRecord) {
                $unmatchedRiderNames[] = $importedResult->riderName;
                continue;
            }

            $matchedResults[] = [$importedResult, $rider];
        }

        if ($importedResults === []) {
            return new ImportStageResultsReport($stageNumber, 0, []);
        }

        if ($unmatchedRiderNames !== []) {
            return new ImportStageResultsReport($stageNumber, 0, array_values(array_unique($unmatchedRiderNames)));
        }

        $this->results->deleteByStage($stage);
        $matchedRiderIds = [];

        foreach ($matchedResults as [$importedResult, $rider]) {
            /** @var ImportedStageResult $importedResult */
            /** @var RiderRecord $rider */
            $matchedRiderIds[] = $rider->id();
            $this->results->save(new StageRiderResultRecord(
                $stage,
                $rider,
                $importedResult->timeInSeconds,
                $importedResult->gapInSeconds,
            ));
        }

        $this->markRidersWithoutStageTimeAsAbandoned($matchedRiderIds);
        $this->entityManager->flush();

        return new ImportStageResultsReport($stageNumber, count($matchedResults), []);
    }

    /**
     * @param list<int> $matchedRiderIds
     */
    private function markRidersWithoutStageTimeAsAbandoned(array $matchedRiderIds): void
    {
        foreach ($this->riders->findAllOrderedByName() as $rider) {
            if (!$rider->isStillRacing()) {
                continue;
            }

            if (in_array($rider->id(), $matchedRiderIds, true)) {
                continue;
            }

            $rider->markAbandoned();
        }
    }
}
