<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\LeagueDashboard;
use App\Application\DTO\RiderStageStandingItem;
use App\Application\DTO\StageLeagueDashboard;
use App\Application\DTO\StageSummary;
use App\Application\DTO\StandingItem;
use App\Application\Query\GetLeagueDashboardQuery;
use App\Application\Repository\FantasyTeamRepositoryInterface;
use App\Application\Service\CountryFlagResolver;
use App\Domain\Exception\FantasyLeagueNotFound;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Entity\FantasyLeagueRecord;
use App\Infrastructure\Doctrine\Entity\FantasyTeamRecord;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Entity\StageRecord;
use App\Infrastructure\Doctrine\Entity\StageRiderResultRecord;
use App\Infrastructure\Doctrine\Repository\FantasyLeagueRecordRepository;
use App\Infrastructure\Doctrine\Repository\StageRecordRepository;
use App\Infrastructure\Doctrine\Repository\StageRiderResultRecordRepository;

final readonly class GetLeagueDashboardHandler
{
    public function __construct(
        private FantasyLeagueRecordRepository $leagues,
        private FantasyTeamRepositoryInterface $teams,
        private StageRecordRepository $stages,
        private StageRiderResultRecordRepository $results,
        private CountryFlagResolver $countryFlags,
    ) {
    }

    public function __invoke(GetLeagueDashboardQuery $query): LeagueDashboard
    {
        $league = $this->leagues->find($query->leagueId);

        if (!$league instanceof FantasyLeagueRecord || !$league->hasParticipant($query->user)) {
            throw new FantasyLeagueNotFound();
        }

        $teams = $this->teamsFor($league);

        return new LeagueDashboard(
            $league->name(),
            $league->code(),
            $this->generalStandings($teams),
            array_map(
                fn (StageRecord $stage): StageLeagueDashboard => $this->stageDashboard($stage, $teams),
                $this->stages->findAllOrderedByNumber(),
            ),
        );
    }

    /**
     * @return array<string, FantasyTeamRecord>
     */
    private function teamsFor(FantasyLeagueRecord $league): array
    {
        $teams = [];

        foreach ($league->participants() as $participant) {
            $team = $this->teams->findOneByOwner($participant);
            if ($team instanceof FantasyTeamRecord) {
                $teams[$participant->username()] = $team;
            }
        }

        return $teams;
    }

    /**
     * @param array<string, FantasyTeamRecord> $teams
     * @return list<StandingItem>
     */
    private function generalStandings(array $teams): array
    {
        $standings = [];

        foreach ($teams as $ownerUsername => $team) {
            $times = $this->results->totalTimesByRiderIds($team->riders());
            $standings[] = [
                'team' => $team,
                'ownerUsername' => $ownerUsername,
                'time' => array_sum($times),
            ];
        }

        return $this->rankTeamRows($standings);
    }

    /**
     * @param array<string, FantasyTeamRecord> $teams
     */
    private function stageDashboard(StageRecord $stage, array $teams): StageLeagueDashboard
    {
        $teamRows = [];
        foreach ($teams as $ownerUsername => $team) {
            $times = $this->results->stageTimesByRiderIds($stage, $team->riders());
            $teamRows[] = [
                'team' => $team,
                'ownerUsername' => $ownerUsername,
                'time' => array_sum($times),
            ];
        }

        return new StageLeagueDashboard(
            new StageSummary(
                $stage->number(),
                $stage->startLocation(),
                $stage->finishLocation(),
                $stage->distanceInKilometers(),
                $stage->positiveElevationInMeters(),
            ),
            $this->rankTeamRows($teamRows),
            $this->riderStandings($stage),
        );
    }

    /**
     * @param list<array{team: FantasyTeamRecord, ownerUsername: string, time: int}> $rows
     * @return list<StandingItem>
     */
    private function rankTeamRows(array $rows): array
    {
        usort($rows, static fn (array $left, array $right): int => $left['time'] <=> $right['time']);

        $ranked = [];
        $rank = 1;
        foreach ($rows as $row) {
            $ranked[] = new StandingItem(
                $rank,
                $row['team']->name(),
                $row['ownerUsername'],
                $this->spentBudget($row['team']),
                $row['time'],
                $this->formatDuration($row['time']),
            );
            ++$rank;
        }

        return $ranked;
    }

    /**
     * @return list<RiderStageStandingItem>
     */
    private function riderStandings(StageRecord $stage): array
    {
        $ranked = [];
        $rank = 1;

        foreach ($this->results->findByStage($stage) as $result) {
            $ranked[] = new RiderStageStandingItem(
                $rank,
                $result->rider()->name(),
                $this->countryFlags->forNationality($result->rider()->nationality()),
                $result->rider()->realTeam(),
                $this->formatDuration($result->timeInSeconds()),
            );
            ++$rank;
        }

        return $ranked;
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
}
