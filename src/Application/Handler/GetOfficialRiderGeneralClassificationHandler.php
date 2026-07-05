<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\OfficialRiderStandingItem;
use App\Application\DTO\RiderListItem;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;

final readonly class GetOfficialRiderGeneralClassificationHandler
{
    public function __construct(
        private ListRidersHandler $riders,
    ) {
    }

    /**
     * @return list<OfficialRiderStandingItem>
     */
    public function __invoke(?ApplicationUser $user = null): array
    {
        $riders = ($this->riders)($user);

        usort($riders, static function (RiderListItem $left, RiderListItem $right): int {
            if ($left->isStillRacing !== $right->isStillRacing) {
                return $left->isStillRacing ? -1 : 1;
            }

            if ($left->totalTimeInSeconds === null && $right->totalTimeInSeconds === null) {
                return $left->name <=> $right->name;
            }

            if ($left->totalTimeInSeconds === null) {
                return 1;
            }

            if ($right->totalTimeInSeconds === null) {
                return -1;
            }

            return $left->totalTimeInSeconds <=> $right->totalTimeInSeconds;
        });

        $standings = [];
        $rank = 1;
        foreach ($riders as $rider) {
            $standings[] = new OfficialRiderStandingItem(
                $rank,
                $rider->name,
                $rider->nationalityFlag,
                $rider->realTeam,
                $rider->formattedTotalTime,
                $rider->formattedTotalGap,
                $rider->isInCurrentUserTeam,
                $rider->isStillRacing,
            );
            ++$rank;
        }

        return $standings;
    }
}
