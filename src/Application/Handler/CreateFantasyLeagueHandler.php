<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\Command\CreateFantasyLeagueCommand;
use App\Application\Repository\FantasyLeagueRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\FantasyLeagueRecord;
use Random\Randomizer;

final readonly class CreateFantasyLeagueHandler
{
    public function __construct(
        private FantasyLeagueRepositoryInterface $leagues,
    ) {
    }

    public function __invoke(CreateFantasyLeagueCommand $command): FantasyLeagueRecord
    {
        do {
            $code = $this->generateCode();
        } while ($this->leagues->findOneByCode($code) instanceof FantasyLeagueRecord);

        $league = new FantasyLeagueRecord($command->creator, trim($command->name), $code);
        $this->leagues->save($league);

        return $league;
    }

    private function generateCode(): string
    {
        return (new Randomizer())->getBytesFromString('ABCDEFGHJKLMNPQRSTUVWXYZ23456789', 8);
    }
}
