<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\LeagueTeamCompositionItem;
use App\Application\DTO\LeagueTeamCompositionRiderItem;
use App\Application\DTO\LeagueTeamCompositions;
use App\Application\Query\GetLeagueTeamCompositionsQuery;
use App\Application\Repository\FantasyTeamRepositoryInterface;
use App\Application\Service\CountryFlagResolver;
use App\Domain\Exception\FantasyLeagueNotFound;
use App\Infrastructure\Doctrine\Entity\FantasyLeagueRecord;
use App\Infrastructure\Doctrine\Entity\FantasyTeamRecord;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Repository\FantasyLeagueRecordRepository;

final readonly class GetLeagueTeamCompositionsHandler
{
    public function __construct(
        private FantasyLeagueRecordRepository $leagues,
        private FantasyTeamRepositoryInterface $teams,
        private CountryFlagResolver $countryFlags,
    ) {
    }

    public function __invoke(GetLeagueTeamCompositionsQuery $query): LeagueTeamCompositions
    {
        $league = $this->leagues->find($query->leagueId);

        if (!$league instanceof FantasyLeagueRecord || !$league->hasParticipant($query->user)) {
            throw new FantasyLeagueNotFound();
        }

        $teams = [];
        foreach ($league->participants() as $participant) {
            $team = $this->teams->findOneByOwner($participant);
            if (!$team instanceof FantasyTeamRecord) {
                continue;
            }

            $teams[] = new LeagueTeamCompositionItem(
                $team->name(),
                $participant->username(),
                $this->spentBudget($team),
                $participant->username() === $query->user->username(),
                $this->riders($team),
            );
        }

        usort(
            $teams,
            static fn (LeagueTeamCompositionItem $left, LeagueTeamCompositionItem $right): int => $left->teamName <=> $right->teamName,
        );

        return new LeagueTeamCompositions($league->name(), $league->code(), $teams);
    }

    private function spentBudget(FantasyTeamRecord $team): int
    {
        return array_sum(array_map(
            static fn (RiderRecord $rider): int => $rider->marketValueInEuros(),
            $team->riders(),
        ));
    }

    /**
     * @return list<LeagueTeamCompositionRiderItem>
     */
    private function riders(FantasyTeamRecord $team): array
    {
        $riders = $team->riders();
        usort(
            $riders,
            static fn (RiderRecord $left, RiderRecord $right): int => $right->marketValueInEuros() <=> $left->marketValueInEuros(),
        );

        return array_map(
            fn (RiderRecord $rider): LeagueTeamCompositionRiderItem => new LeagueTeamCompositionRiderItem(
                $rider->name(),
                $rider->nationality(),
                $this->countryFlags->forNationality($rider->nationality()),
                $rider->realTeam(),
                $rider->marketValueInEuros(),
                $rider->specialty()?->value,
                $rider->isStillRacing(),
            ),
            $riders,
        );
    }
}
