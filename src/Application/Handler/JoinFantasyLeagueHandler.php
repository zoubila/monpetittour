<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\JoinFantasyLeagueCommand;
use App\Application\Repository\FantasyLeagueRepositoryInterface;
use App\Domain\Exception\FantasyLeagueNotFound;
use App\Infrastructure\Doctrine\Entity\FantasyLeagueRecord;

final readonly class JoinFantasyLeagueHandler
{
    public function __construct(
        private FantasyLeagueRepositoryInterface $leagues,
    ) {
    }

    public function __invoke(JoinFantasyLeagueCommand $command): FantasyLeagueRecord
    {
        $league = $this->leagues->findOneByCode($command->code);

        if (!$league instanceof FantasyLeagueRecord) {
            throw new FantasyLeagueNotFound();
        }

        $league->addParticipant($command->participant);
        $this->leagues->save($league);

        return $league;
    }
}
