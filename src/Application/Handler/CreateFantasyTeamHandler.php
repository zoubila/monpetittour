<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\CreateFantasyTeamCommand;
use App\Domain\Entity\FantasyTeam;
use App\Domain\Entity\FantasyUser;
use App\Domain\Exception\FantasyTeamAlreadyExists;
use App\Application\Repository\FantasyTeamRepositoryInterface;
use App\Application\Repository\RiderReadRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\FantasyTeamRecord;

final readonly class CreateFantasyTeamHandler
{
    public function __construct(
        private FantasyTeamRepositoryInterface $teams,
        private RiderReadRepositoryInterface $riders,
    ) {
    }

    public function __invoke(CreateFantasyTeamCommand $command): FantasyTeamRecord
    {
        if ($this->teams->findOneByOwner($command->owner) instanceof FantasyTeamRecord) {
            throw new FantasyTeamAlreadyExists();
        }

        $riderIds = array_values(array_unique($command->riderIds));
        $selectedRiders = $this->riders->domainRidersByIds($riderIds);

        new FantasyTeam($command->name, new FantasyUser($command->owner->username()), $selectedRiders);

        return $this->teams->create($command->owner, trim($command->name), $riderIds);
    }
}
