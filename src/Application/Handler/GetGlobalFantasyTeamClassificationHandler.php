<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\StandingItem;
use App\Application\Repository\FantasyTeamRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Entity\FantasyTeamRecord;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Repository\StageRiderResultRecordRepository;

final readonly class GetGlobalFantasyTeamClassificationHandler
{
    public function __construct(
        private FantasyTeamRepositoryInterface $teams,
        private StageRiderResultRecordRepository $results,
    ) {
    }

    /**
     * @return list<StandingItem>
     */
    public function __invoke(ApplicationUser $user): array
    {
        $rows = [];

        foreach ($this->teams->findAllTeams() as $team) {
            $rows[] = [
                'team' => $team,
                'ownerUsername' => $team->owner()->username(),
                'time' => array_sum($this->results->totalTimesByRiderIds($team->riders())),
            ];
        }

        usort($rows, static fn (array $left, array $right): int => $left['time'] <=> $right['time']);

        $standings = [];
        $rank = 1;
        $bestTime = $rows[0]['time'] ?? 0;
        foreach ($rows as $row) {
            $gapInSeconds = $row['time'] - $bestTime;
            $standings[] = new StandingItem(
                $rank,
                $row['team']->name(),
                $row['ownerUsername'],
                $this->spentBudget($row['team']),
                $row['time'],
                $this->formatDuration($row['time']),
                $gapInSeconds,
                $this->formatGap($gapInSeconds),
                $row['ownerUsername'] === $user->username(),
            );
            ++$rank;
        }

        return $standings;
    }

    private function spentBudget(FantasyTeamRecord $team): int
    {
        return array_sum(array_map(
            static fn (RiderRecord $rider): int => $rider->marketValueInEuros(),
            $team->riders(),
        ));
    }

    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3_600);
        $remainingSeconds = $seconds % 3_600;
        $minutes = intdiv($remainingSeconds, 60);
        $seconds = $remainingSeconds % 60;

        return sprintf('%02dh %02dmin %02ds', $hours, $minutes, $seconds);
    }

    private function formatGap(int $seconds): string
    {
        if ($seconds <= 0) {
            return '-';
        }

        return sprintf('+ %s', $this->formatShortDuration($seconds));
    }

    private function formatShortDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3_600);
        $remainingSeconds = $seconds % 3_600;
        $minutes = intdiv($remainingSeconds, 60);
        $seconds = $remainingSeconds % 60;

        if ($hours > 0) {
            return sprintf('%02dh %02dmin %02ds', $hours, $minutes, $seconds);
        }

        if ($minutes > 0) {
            return sprintf('%02dmin %02ds', $minutes, $seconds);
        }

        return sprintf('%02ds', $seconds);
    }
}
