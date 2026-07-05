<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\RiderListItem;
use App\Application\Repository\FantasyTeamRepositoryInterface;
use App\Application\Repository\RiderResultReadRepositoryInterface;
use App\Application\Repository\RiderReadRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Entity\FantasyTeamRecord;
use App\Infrastructure\Doctrine\Entity\RiderRecord;

final readonly class ListRidersHandler
{
    public function __construct(
        private RiderReadRepositoryInterface $riders,
        private RiderResultReadRepositoryInterface $results,
        private FantasyTeamRepositoryInterface $teams,
    ) {
    }

    /**
     * @return list<RiderListItem>
     */
    public function __invoke(?ApplicationUser $user = null): array
    {
        $riders = $this->riders->listRiders();
        $riderIds = array_map(static fn (RiderListItem $rider): int => $rider->id, $riders);
        $times = $this->results->cumulativeTimesByRiderIds($riderIds);
        $bestTime = $this->results->bestCumulativeTimeInSeconds();
        $currentUserRiderIds = $this->currentUserRiderIds($user);

        return array_map(
            fn (RiderListItem $rider): RiderListItem => $this->withCumulativeStats(
                $rider,
                $times[$rider->id] ?? null,
                $bestTime,
                in_array($rider->id, $currentUserRiderIds, true),
            ),
            $riders,
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

    private function withCumulativeStats(
        RiderListItem $rider,
        ?int $totalTime,
        ?int $bestTime,
        bool $isInCurrentUserTeam,
    ): RiderListItem {
        $totalGap = $totalTime !== null && $bestTime !== null ? $totalTime - $bestTime : null;

        return new RiderListItem(
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
            $isInCurrentUserTeam,
            $rider->isStillRacing,
        );
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
