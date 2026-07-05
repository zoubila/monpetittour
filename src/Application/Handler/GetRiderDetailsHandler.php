<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\RiderDetails;
use App\Application\Query\GetRiderDetailsQuery;
use App\Application\Repository\FantasyTeamRepositoryInterface;
use App\Application\Repository\RiderResultReadRepositoryInterface;
use App\Application\Repository\RiderReadRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Entity\FantasyTeamRecord;
use App\Infrastructure\Doctrine\Entity\RiderRecord;

final readonly class GetRiderDetailsHandler
{
    public function __construct(
        private RiderReadRepositoryInterface $riders,
        private RiderResultReadRepositoryInterface $results,
        private FantasyTeamRepositoryInterface $teams,
    ) {
    }

    public function __invoke(GetRiderDetailsQuery $query, ?ApplicationUser $user = null): ?RiderDetails
    {
        $rider = $this->riders->riderDetailsBySlug($query->slug);
        if (!$rider instanceof RiderDetails) {
            return null;
        }

        $times = $this->results->cumulativeTimesByRiderIds([$rider->id]);
        $totalTime = $times[$rider->id] ?? null;
        $bestTime = $this->results->bestCumulativeTimeInSeconds();
        $totalGap = $totalTime !== null && $bestTime !== null ? $totalTime - $bestTime : null;

        return new RiderDetails(
            $rider->id,
            $rider->slug,
            $rider->name,
            $rider->realTeam,
            $rider->nationality,
            $rider->nationalityFlag,
            $rider->marketValueInEuros,
            $rider->specialty,
            $totalTime,
            $totalTime !== null ? $this->formatDuration($totalTime) : 'Aucun temps',
            $totalGap,
            $totalGap !== null ? $this->formatGap($totalGap) : '-',
            in_array($rider->id, $this->currentUserRiderIds($user), true),
        );
    }

    /**
     * @return list<int>
     */
    private function currentUserRiderIds(?ApplicationUser $user): array
    {
        if (!$user instanceof ApplicationUser) {
            return [];
        }

        $team = $this->teams->findOneByOwner($user);
        if (!$team instanceof FantasyTeamRecord) {
            return [];
        }

        return array_map(static fn (RiderRecord $rider): int => $rider->id(), $team->riders());
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
