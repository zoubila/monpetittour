<?php

declare(strict_types=1);

namespace App\Tests;

use App\Domain\Entity\FantasyTeam;
use App\Infrastructure\Fixture\FantasyFixtureCatalog;
use PHPUnit\Framework\TestCase;

final class FantasyFixtureCatalogTest extends TestCase
{
    public function testFixturesPrepareTheExpectedFantasyModel(): void
    {
        $catalog = new FantasyFixtureCatalog();

        self::assertGreaterThanOrEqual(20, count($catalog->riders()));
        self::assertCount(4, $catalog->stages());
        self::assertSame('Ligue des Copains', $catalog->mainLeague()->name);
        self::assertCount(2, $catalog->mainLeague()->teams);

        foreach ($catalog->mainLeague()->teams as $team) {
            self::assertCount(8, $team->riders);
            self::assertLessThanOrEqual(FantasyTeam::BUDGET_IN_EUROS, $team->spentBudgetInEuros());
        }
    }
}
