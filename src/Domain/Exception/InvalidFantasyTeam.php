<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class InvalidFantasyTeam extends DomainException
{
    public static function mustContainEightRiders(): self
    {
        return new self('Une équipe fantasy doit contenir exactement 8 coureurs.');
    }

    public static function budgetExceeded(): self
    {
        return new self('Le budget fantasy de 1 200 000 € est dépassé.');
    }
}
