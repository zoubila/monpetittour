<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class InvalidFantasyTeam extends DomainException
{
    public static function mustContainEightRiders(): self
    {
        return new self('error.team_must_contain_eight_riders');
    }

    public static function budgetExceeded(): self
    {
        return new self('error.team_budget_exceeded');
    }
}
