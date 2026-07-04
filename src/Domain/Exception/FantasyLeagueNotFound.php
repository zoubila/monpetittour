<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class FantasyLeagueNotFound extends DomainException
{
    public function __construct()
    {
        parent::__construct('Aucune ligue ne correspond à ce code.');
    }
}
