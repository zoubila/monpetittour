<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\HomeDashboard;
use App\Application\DTO\RiderListItem;
use App\Application\DTO\StandingItem;
use App\Application\DTO\UserLeagueSummary;
use App\Application\DTO\UserTeamSummary;
use App\Application\Repository\FantasyLeagueRepositoryInterface;
use App\Application\Repository\FantasyTeamRepositoryInterface;
use App\Application\Repository\RiderReadRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Entity\FantasyLeagueRecord;
use App\Infrastructure\Doctrine\Entity\FantasyTeamRecord;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Repository\StageRiderResultRecordRepository;
use App\Infrastructure\Doctrine\Repository\StageRecordRepository;

final readonly class GetHomeDashboardHandler
{
    public function __construct(
        private RiderReadRepositoryInterface $riderRecords,
        private FantasyTeamRepositoryInterface $teams,
        private FantasyLeagueRepositoryInterface $leagues,
        private StageRecordRepository $stages,
        private StageRiderResultRecordRepository $results,
    ) {
    }

    public function __invoke(ApplicationUser $user): HomeDashboard
    {
        $team = $this->teams->findOneByOwner($user);

        return new HomeDashboard(
            $team instanceof FantasyTeamRecord ? $this->teamSummary($team) : null,
            $this->riderRecords->countRiders(),
            $this->stages->count([]),
            array_map(
                fn (FantasyLeagueRecord $league): UserLeagueSummary => $this->leagueSummary($league),
                $this->leagues->findByParticipant($user),
            ),
        );
    }

    private function teamSummary(FantasyTeamRecord $team): UserTeamSummary
    {
        $riders = $this->ridersForRecords($team->riders());

        return new UserTeamSummary(
            $team->name(),
            array_sum(array_map(static fn (RiderListItem $rider): int => $rider->marketValueInEuros, $riders)),
            $riders,
        );
    }

    private function leagueSummary(FantasyLeagueRecord $league): UserLeagueSummary
    {
        $rows = [];

        foreach ($league->participants() as $participant) {
            $team = $this->teams->findOneByOwner($participant);
            if (!$team instanceof FantasyTeamRecord) {
                continue;
            }

            $rows[] = [
                'team' => $team,
                'ownerUsername' => $participant->username(),
                'time' => array_sum($this->results->totalTimesByRiderIds($team->riders())),
            ];
        }

        usort($rows, static fn (array $left, array $right): int => $left['time'] <=> $right['time']);

        $standings = [];
        $rank = 1;
        foreach ($rows as $row) {
            $standings[] = new StandingItem(
                $rank,
                $row['team']->name(),
                $row['ownerUsername'],
                $this->teamSummary($row['team'])->spentBudgetInEuros,
                $row['time'],
                $this->formatDuration($row['time']),
            );
            ++$rank;
        }

        return new UserLeagueSummary($league->id(), $league->name(), $league->code(), $standings);
    }

    /**
     * @param list<RiderRecord> $records
     * @return list<RiderListItem>
     */
    private function ridersForRecords(array $records): array
    {
        $riders = [];

        foreach ($records as $rider) {
            $riders[] = new RiderListItem(
                $rider->id(),
                $rider->slug(),
                $rider->name(),
                $rider->realTeam(),
                $rider->nationality(),
                $rider->marketValueInEuros(),
                $rider->specialty()?->value,
            );
        }

        return $riders;
    }

    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3_600);
        $remainingSeconds = $seconds % 3_600;
        $minutes = intdiv($remainingSeconds, 60);
        $seconds = $remainingSeconds % 60;

        return sprintf('%02dh %02dmin %02ds', $hours, $minutes, $seconds);
    }
}
