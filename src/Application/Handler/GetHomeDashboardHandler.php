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
use App\Application\Service\CountryFlagResolver;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Entity\FantasyLeagueRecord;
use App\Infrastructure\Doctrine\Entity\FantasyTeamRecord;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Repository\StageRiderResultRecordRepository;
use App\Infrastructure\Doctrine\Repository\StageRecordRepository;

final readonly class GetHomeDashboardHandler
{
    public function __construct(
        private FantasyTeamRepositoryInterface $teams,
        private FantasyLeagueRepositoryInterface $leagues,
        private StageRecordRepository $stages,
        private StageRiderResultRecordRepository $results,
        private CountryFlagResolver $countryFlags,
        private GetGlobalFantasyTeamClassificationHandler $globalFantasyTeamClassification,
    ) {
    }

    public function __invoke(ApplicationUser $user): HomeDashboard
    {
        $team = $this->teams->findOneByOwner($user);
        $globalStanding = $this->currentUserGlobalStanding($user);

        return new HomeDashboard(
            $team instanceof FantasyTeamRecord ? $this->teamSummary($team) : null,
            $this->results->countStagesWithResults(),
            $this->stages->count([]),
            $team instanceof FantasyTeamRecord ? $this->formatDuration($this->teamTotalTimeInSeconds($team)) : 'Aucune équipe',
            $globalStanding?->rank,
            array_map(
                fn (FantasyLeagueRecord $league): UserLeagueSummary => $this->leagueSummary($league, $user->username()),
                $this->leagues->findByParticipant($user),
            ),
        );
    }

    private function currentUserGlobalStanding(ApplicationUser $user): ?StandingItem
    {
        foreach (($this->globalFantasyTeamClassification)($user) as $standing) {
            if ($standing->isCurrentUser) {
                return $standing;
            }
        }

        return null;
    }

    private function teamTotalTimeInSeconds(FantasyTeamRecord $team): int
    {
        return array_sum($this->results->totalTimesByRiderIds($team->riders()));
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

    private function leagueSummary(FantasyLeagueRecord $league, string $currentUsername): UserLeagueSummary
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
        $bestTime = $rows[0]['time'] ?? 0;
        foreach ($rows as $row) {
            $gapInSeconds = $row['time'] - $bestTime;
            $standings[] = new StandingItem(
                $rank,
                $row['team']->name(),
                $row['ownerUsername'],
                $this->teamSummary($row['team'])->spentBudgetInEuros,
                $row['time'],
                $this->formatDuration($row['time']),
                $gapInSeconds,
                $this->formatGap($gapInSeconds),
                $row['ownerUsername'] === $currentUsername,
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
        usort($records, static fn (RiderRecord $left, RiderRecord $right): int => $right->marketValueInEuros() <=> $left->marketValueInEuros());

        $riders = [];
        $riderIds = array_map(static fn (RiderRecord $rider): int => $rider->id(), $records);
        $times = $this->results->cumulativeTimesByRiderIds($riderIds);
        $bestTime = $this->results->bestCumulativeTimeInSeconds();

        foreach ($records as $rider) {
            $totalTime = $times[$rider->id()] ?? null;
            $totalGap = $totalTime !== null && $bestTime !== null ? $totalTime - $bestTime : null;
            $riders[] = new RiderListItem(
                $rider->id(),
                $rider->slug(),
                $rider->name(),
                $rider->realTeam(),
                $rider->nationality(),
                $this->countryFlags->forNationality($rider->nationality()),
                $rider->marketValueInEuros(),
                $rider->specialty()?->value,
                $totalTime,
                $totalTime !== null ? $this->formatDuration($totalTime) : 'Aucun temps',
                $totalGap,
                $totalGap !== null ? $this->formatGap($totalGap) : '-',
                true,
                $rider->isStillRacing(),
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
